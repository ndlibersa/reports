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

    public function __construct($id){
        if ($id === null) {
            //throw new BadMethodCallException("Report constructor did not receive a valid id.");
        }

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
    }

    public function run(array $ignoreCols, $isArchive, array $addWhere, $sortColumn, $sortOrder, $reportTable){
        if ($isArchive) {
            $orderBy = '';
        } else if ($sortColumn) {
            if (isset($reportTable)) {
                $orderBy = "ORDER BY " . $reportTable->fieldAt($sortColumn);
            } else {
                $orderBy = "ORDER BY $sortColumn $sortOrder";
            }
        } else {
            error_log("sortColumn was not given a default value! Default fallback no longer works properly!");
            $orderBy = $this->orderby;
        }
        $sql = $this->sql;
        foreach ($ignoreCols as $COL) {
            if (stripos(" $COL",$sql)) {
                $sql = preg_replace("[ ,]?$COL", "",$sql, $limit=1);
            }
        }

        if (stripos($this->sql, 'mus')) {
            $field = 'mus.archiveInd = ' . (0+$isArchive);
        } else {
            $field = 'yus.archiveInd = ' . (0+$isArchive);
        }
        $sql = str_replace('ADD_WHERE2', $addWhere[1], $sql);
        $sql = str_replace('ADD_WHERE', "{$addWhere[0]} AND $field", $sql);
        $sql .= " $orderBy";
        $db = new DBService(Config::$database->{$this->dbname});
        return $db->query("$sql");
    }

    // returns outlier array for display at the bottom of reports
    public function getOutliers(){
        Config::init();
        $outlier = array();
        foreach ( $this->db
                ->selectDB(Config::$database->{$this->dbname})
                ->query("SELECT outlierLevel, overageCount, overagePercent FROM Outlier ORDER BY 2")
                ->fetchRows(MYSQLI_ASSOC) as $outlierArray ){
            $outlier[$outlierArray['outlierLevel']]['overageCount'] = $outlierArray['overageCount'];
            $outlier[$outlierArray['outlierLevel']]['overagePercent'] = $outlierArray['overagePercent'];
            $outlier[$outlierArray['outlierLevel']]['outlierLevel'] = $outlierArray['outlierLevel'];
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
            $objects[] = new ReportParameter($row['reportParameterID']);
        }
        return $objects;
    }

    // removes associated parameters
    public function getGroupingColumns(array $ignoreList){
        // set database to reporting database name
        Config::init();
        $this->db->selectDB(Config::$database->name);
        // Get the report grouping columns into groupColsArray for faster lookup later
        // returns array of objects
        $groupColsArray = array();
        $exceptions = implode("', '",$ignoreList);
        foreach ( $this->db
                ->query("SELECT reportGroupingColumnName
                        FROM ReportGroupingColumn
                        WHERE reportID = '{$this->id}'
                        AND reportGroupingColumnName NOT IN ('$exceptions')"
                    )
                ->fetchRows(MYSQLI_ASSOC) as $row ){
            $groupColsArray[$row['reportGroupingColumnName']] = false;
        }
        return $groupColsArray;
    }

    // removes associated parameters
    public function getReportSums($ignoreList){
        Config::init();
        // Get the report summing columns into sumColsArray for faster lookup later
        // returns array of objects
        $sumColsArray = array();
        $exceptions = implode("', '",$ignoreList);
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
        return $sumColsArray;
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
}
?>
