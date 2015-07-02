<?php

/*
 * *************************************************************************************************************************
 * * CORAL Usage Statistics Reporting Module v. 1.0
 * *
 * * Copyright (c) 2010 University of Notre Dame
 * *
 * * This file is part of CORAL.
 * *
 * * CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * *
 * * CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * *
 * * You should have received a copy of the GNU General Public License along with CORAL. If not, see <http://www.gnu.org/licenses/>.
 * *
 * *************************************************************************************************************************
 */
class Report {
    public $db;
    public $id;
    public $name;
    public $dbname;
    public $sql;
    public $hasGroupTotalInd;
    public $orderby;
    public $infoText;
    public $ignoredCols = array();
    public $addWhere = array('','');
    public $sort = array('order'=>'asc','column'=>1);
    public $table;
    public $titleID = null;
    public $baseURL = null;
    public $showUnadjusted = false;
    public $onlySummary = false;
    public $flagManualSubtotal = false;

    public function __construct($id){
        //if ($id === null) {
            //throw new BadMethodCallException("Report constructor did not receive a valid id.");
        //}

        $this->db = new DBService();
        $result = $this->db
            ->query("SELECT * FROM Report WHERE reportID = '$id' LIMIT 1")
            ->fetchRow(MYSQLI_ASSOC);

        $this->id = $id;
        $this->name = $result['reportName'];
        $this->dbname = $result['reportDatabaseName'];
        $this->hasGroupTotalInd = ($result['groupTotalInd'] === '1') ? true : false;
        $this->orderby = $result['orderBySQL'];
        $this->infoText = $result['infoDisplayText'];
        $this->sql = $result['reportSQL'];

        ReportNotes::init($this->dbname);

        if (isset($_REQUEST['titleID']) && $_REQUEST['titleID']!==null && $_REQUEST['titleID']!=='') {
            $this->titleID = $_REQUEST['titleID'];
            FormInputs::addVisible('titleID',$this->titleID);
        }

        if (isset($_REQUEST['sortColumn'])) {
            $this->sort['column'] = $_REQUEST['sortColumn'];
        }

        if (isset($_REQUEST['sortOrder'])) {
            $this->sort['order'] = $_REQUEST['sortOrder'];
        }

        FormInputs::addVisible('reportID',$this->id);
        FormInputs::addHidden('useHidden',1);
        FormInputs::addHidden('sortColumn',$this->sort['column']);
        FormInputs::addHidden('sortOrder',$this->sort['order']);

        Config::init();
        if (Config::$settings->baseURL) {
            if (strpos(Config::$settings->baseURL, '?') > 0) {
                $this->baseURL = Config::$settings->baseURL . '&';
            } else {
                $this->baseURL = Config::$settings->baseURL .'?';
            }
        }
    }

    public function run($isArchive, $allowSort){
        /*if ($isArchive) {
            $orderBy = '';
        } else if (isset($this->table)) {
            $orderBy = "ORDER BY " . $this->table->fieldAt($this->sort['column']);
        } else {*/
            $orderBy = "ORDER BY {$this->sort['column']} {$this->sort['order']}";
        //}
        $sql = $this->sql;
        foreach ($this->ignoredCols as $COL) {
            if (stripos(" $COL",$sql)!==FALSE) {
                $sql = preg_replace("[ ,]?$COL", "",$sql, $limit=1);
            }
        }

        if ($allowSort)
            $sql .= " $orderBy";
        $sql = str_replace('ADD_WHERE2', $this->addWhere[1], $sql);

        if (stripos($this->sql, 'mus')!==FALSE) {
            $field = 'mus.archiveInd = ' . intval($isArchive);
        } else {
            $field = 'yus.archiveInd = ' . intval($isArchive);
        }

        $sql = str_replace('ADD_WHERE', "{$this->addWhere[0]} AND $field", $sql);
        $db = new DBService(Config::$database->{$this->dbname});
        $reportArray = $db->query("$sql");
        $this->table = new ReportTable($this, $reportArray->fetchFields());
        return $reportArray;
    }

    public function needToGroupRow($outputType,$performCheck,$print_subtotal_flag) {
        return $outputType != 'xls'
            && !($performCheck && in_array(false, $this->table->columnData['group'], true) !== false)
            && $print_subtotal_flag
            && $this->hasGroupTotalInd;
    }

    // returns outlier array for display at the bottom of reports
    public function getOutliers(){
        Config::init();
        $outlier = array();
        foreach ( $this->db
                ->selectDB(Config::$database->{$this->dbname})
                ->query("SELECT outlierLevel, overageCount, overagePercent FROM Outlier ORDER BY 2")
                ->fetchRows(MYSQLI_ASSOC) as $outlierArray ){
            $outlier[$outlierArray['outlierLevel']]['count'] = $outlierArray['overageCount'];
            $outlier[$outlierArray['outlierLevel']]['percent'] = $outlierArray['overagePercent'];
            $outlier[$outlierArray['outlierLevel']]['level'] = $outlierArray['outlierLevel'];
        }
        return $outlier;
    }

    // returns associated parameters
    public function getParameters(){
        // set database to reporting database name
        Config::init();
        $this->db->selectDB(Config::$database->name);

        $objects = array();
        foreach ( $this->db
                ->query("SELECT reportParameterID
                    FROM ReportParameter
                    WHERE reportID = '{$this->id}'
                    ORDER BY 1")
                ->fetchRows(MYSQLI_ASSOC) as $row ){
            $objects[] = ParameterFactory::makeParam($row['reportParameterID']);
        }
        $objects[] = new CheckSummaryOnlyParameter($this->id);
        return $objects;
    }

    // removes associated parameters
    public function getColumnData(){
        // set database to reporting database name
        Config::init();
        $this->db->selectDB(Config::$database->name);
        // Get the report grouping columns into groupColsArray for faster lookup later
        // returns array of objects
        $groupColsArray = array();
        $exceptions = implode("', '",$this->ignoredCols);
        foreach ( $this->db
                ->query("SELECT reportGroupingColumnName
                        FROM ReportGroupingColumn
                        WHERE reportID = '{$this->id}'
                        AND reportGroupingColumnName NOT IN ('$exceptions')"
                    )
                ->fetchRows(MYSQLI_ASSOC) as $row ){
            $groupColsArray[$row['reportGroupingColumnName']] = false;
        }
        $sumColsArray = array();
        foreach($this->db
                ->selectDB(Config::$database->name)
                ->query("SELECT reportColumnName, reportAction
                        FROM ReportSum
                        WHERE reportID = '{$this->id}'
                        AND reportColumnName NOT IN ('$exceptions')"
                    )
                ->fetchRows(MYSQLI_ASSOC) as $row ){
            $sumColsArray[$row['reportColumnName']] = $row['reportAction'];
        }

        return array('group'=>$groupColsArray,'sum'=>$sumColsArray);
    }

    // return the title of the ejournal for this report
    public function getUsageTitle($titleID){
        Config::init();
        $row = $this->db
            ->selectDB(Config::$database->{$this->dbname})
            ->query("SELECT title FROM Title WHERE titleID = '$titleID'")
            ->fetchRow(MYSQLI_ASSOC);
        return $row['title'];
    }

    public function getLinkResolverLink(&$row) {
        if ($row['PRINT_ISSN']) {
            if (($row['ONLINE_ISSN'])) {
                return "{$this->baseURL}rft.issn={$row['PRINT_ISSN']}&rft.eissn={$row['ONLINE_ISSN']}";
            } else {
                return "{$this->baseURL}rft.issn={$row['PRINT_ISSN']}";
            }
        } else {
            return "{$this->baseURL}rft.eissn={$row['ONLINE_ISSN']}";
        }
    }
}
?>
