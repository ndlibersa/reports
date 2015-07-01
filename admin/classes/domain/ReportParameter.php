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
class ReportParameter {
    public static $display = '';
    public static $report = null;

    protected $db;
    public function __construct($reportParameterID){
        $this->db = new DBService();
        $result = $this->db
            ->query("SELECT *
            FROM ReportParameter
            WHERE reportParameterID = '$reportParameterID' LIMIT 1")
            ->fetchRow(MYSQLI_ASSOC);
        $this->ID = $reportParameterID;
        $this->reportID = $result['reportID'];
        $this->displayPrompt = $result['parameterDisplayPrompt'];
        $this->addWhereClause = $result['parameterAddWhereClause'];
        $this->typeCode = $result['parameterTypeCode'];
        $this->formatCode = $result['parameterFormatCode'];
        $this->requiredInd = $result['requiredInd'];
        $this->addWhereNum = $result['parameterAddWhereNumber'];
        $this->sql = $result['parameterSQLStatement'];
        $this->parentReportParameterID = $result['parentReportParameterID'];
        $this->sqlRestriction = $result['parameterSQLRestriction'];

        // report#5 and report#6 don't finish when date ranges are enabled
        if( ($this->reportID!=5&&$this->reportID!=6) && $this->displayPrompt === 'Year') {
            DateRange::Convert($this);
        }
    }

    public static function setReport(Report $report) {
        self::$report = $report;
    }

    public function getValue() {
        if(!isset($_REQUEST["prm_$this->ID"]))
            return null;

        if ($this->typeCode === 'ms' && (!isset($_REQUEST['useHidden']) || $_REQUEST['useHidden'] == null)) {
            return implode("', '", explode(',', str_replace('\\\\', ',', $_REQUEST["prm_$this->ID"])));
        } else if ($this->typeCode === 'dddr') {
            return DateRange::Decode($_REQUEST["prm_$this->ID"]);
        } else {
            return trim($_REQUEST["prm_$this->ID"]);
        }
    }

    // used only for allowing access to admin page
    public function getSelectValues($parentValue) {
        // get report info so we can determine which database to use
        $parmReport = new Report($this->reportID);
        Config::init();

        // if this is a restricted sql dependent on previous value
        if ($this->sqlRestriction != '') {
            if ($parentValue) {
                $parmSQL = str_replace(
                    "PARM", $parentValue,
                    str_replace("ADD_WHERE", $this->sqlRestriction, $this->sql)
                );
            } else {
                $parmSQL = str_replace("ADD_WHERE", "", $this->sql);
            }
        } else {
            $parmSQL = str_replace("ADD_WHERE", "", $this->sql);
        }
        $result = $this->db
            ->selectDB(Config::$database->{$parmReport->dbname})
            ->query($parmSQL)
            ->fetchRows();
        $num_rows = count($result);
        $valueArray = array();
        for ($i = 0; $i < $num_rows; ++$i) {
            $valueArray[] = array('cde' => $result[$i][0],'val' => $result[$i][1]);
        }
        return $valueArray;
    }

    // used only for allowing access to admin page
    public function isParent() {
        // set database to reporting database name
        Config::init();
        $row = $this->db
            ->selectDB(Config::$database->name)
            ->query("SELECT count(*) parent_count
            FROM ReportParameter
            WHERE parentReportParameterID = '{$this->ID}'")
            ->fetchRow(MYSQLI_ASSOC);
        return $row['parent_count'] > 0;
    }

    // removes associated parameters
    public function getChildren() {
        Config::init();
        $result = $this->db
            ->selectDB(Config::$database->name)
            ->query("SELECT reportParameterID
            FROM ReportParameter
            WHERE parentReportParameterID = '{$this->ID}' ORDER BY 1")
            ->fetchRows(MYSQLI_ASSOC);
        $num_rows = count($result);
        $objects = array();
        for ($i = 0; $i < $num_rows; ++$i) {
            $objects[] = new ReportParameter($result[$i]['reportParameterID']);
        }
        return $objects;
    }

    // get the display name of the publisher or platform that was sent in
    public function getPubPlatDisplayName($id){
        // get report info so we can determine which database to use
        $parmReport = new Report($this->reportID);
        Config::init();
        $sql = "select distinct reportDisplayName from ";
        if (substr($id, 0, 2) === 'PB') {
            $sql .= "PublisherPlatform where concat('PB_', publisherPlatformID)";
        } else {
            $sql .= "Platform where concat('PL_', platformID)";
        }
        $sql .= " in ('" . strtoupper($id) . "') order by 1";
        $result = $this->db
            ->selectDB(Config::$database->{$parmReport->dbname})
            ->query($sql)
            ->fetchRows(MYSQLI_ASSOC);
        return array_column($result, 'reportDisplayName');
    }

    public function procChk($prm_value) {
        if (($prm_value === 'on') || ($prm_value === 'Y')) {
            self::$report->showUnadjusted = true;
            FormInputs::$visible->addParam("prm_$this->ID", 'Y');
            self::$display .= '<b>Numbers are not adjusted for use violations</b><br/>';
        }
    }

    public function procLimit($prm_value) {
        self::$report->addWhere[0] = ''; // changed from $add_where. Assumed mistake.
        ReportTable::$maxRows = $prm_value;
        self::$display .= "<b>Limit:</b> Top $prm_value<br/>";
    }

    public function procDddr($prm_value) {
        $months = array(
            'JAN','FEB','MAR','APR','MAY','JUN',
            'JUL','AUG','SEP','OCT','NOV','DEC'
            );
        $monthsUsed = DateRange::getMonthsUsed($prm_value);

        $addWhereNum = intval($this->addWhereNum == 2);
        self::$report->addWhere[$addWhereNum] .= " AND $this->addWhereClause";

        for ($i=0; $i<12; ++$i) {
            if (!isset($monthsUsed[$months[$i]])) {
                self::$report->dropMonths[] = $months[$i];
            }
        }

        FormInputs::$visible->addParam("prm_$this->ID",DateRange::Encode($prm_value));
        $prm_value = $prm_value['m0'] . '/' . $prm_value['y0'] . '-'
            . $prm_value['m1'] . '/' . $prm_value['y1'];
        self::$display .= "<b>{$this->displayPrompt}:</b> '$prm_value'<br/>";
    }

    public function procProviderPublisher($prm_value) {
        $addWhereNum = intval($this->addWhereNum == 2);
        self::$report->addWhere[$addWhereNum] .= " AND $this->addWhereClause";
        $prm_value = strtoupper($prm_value);
        self::$report->addWhere[$addWhereNum] = preg_replace(
            '/PARM/', $prm_value, self::$report->addWhere[$addWhereNum]
        );
        FormInputs::$visible->addParam("prm_$this->ID", $prm_value);
        self::$display .= "<b>{$this->displayPrompt}:</b> "
            . implode(', ', $this->getPubPlatDisplayName($prm_value)) . '<br/>';
    }

    public function procDefault($prm_value) {
        $addWhereNum = intval($this->addWhereNum == 2);
        self::$report->addWhere[$addWhereNum] .= " AND $this->addWhereClause";
        $prm_value = strtoupper($prm_value);
        self::$report->addWhere[$addWhereNum] = preg_replace(
            '/PARM/', $prm_value, self::$report->addWhere[$addWhereNum]
        );
        FormInputs::$visible->addParam("prm_$this->ID", $prm_value);
        self::$display .= "<b>{$this->displayPrompt}:</b> '$prm_value'<br/>";
    }
}
?>
