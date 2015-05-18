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

        if($this->displayPrompt === 'Year') {
            $this->makeDateRange();
        }
    }

    public function printHTMLdateRangePicker() {
        $name = "prm_$this->ID";

        $m = "<option value=1>January</option>
            <option value=2>February</option>
            <option value=3>March</option>
            <option value=4>April</option>
            <option value=5>May</option>
            <option value=6>June</option>
            <option value=7>July</option>
            <option value=8>August</option>
            <option value=9>September</option>
            <option value=10>October</option>
            <option value=11>November</option>
            <option value=12>December</option>";

        $y = "";
        $varname_parentID = "prm_$this->parentReportParameterID";
        $parentID = null;
        if (isset($_GET[$varname_parentID])) {
            $parentID = $_GET[$varname_parentID];
        }

        foreach ($this->getSelectValues($parentID) as $value) {
            $y .= "<option value='{$value['cde']}'>{$value['val']}</option>";
        }
        $cls_event = "class='opt' onchange='javascript:updateDateRange";
        echo "<div id='div_parm_$this->ID' class='param'>
            <br><fieldset>
            <legend>From date:</legend>
            <select id='from_date_month' name=\"{$name}[from][month]\" value=1 $cls_event(true);' >$m</select>
            <select id='from_date_year' name=\"{$name}[from][year]\" value='' $cls_event(true);' >$y</select>
            </fieldset>
            <br><fieldset>
            <legend>Through date:</legend>
            <select id='to_date_month' name=\"{$name}[to][month]\" value=12 $cls_event(false);' >$m</select>
            <select id='to_date_year' name=\"{$name}[to][year]\" value='' $cls_event(false);'>$y</select>
            </fieldset>
            </div>";
    }

    private function makeDateRange() {
        $this->requiredInd = 1;
        $this->displayPrompt = "Date Range";
        $this->typeCode = 'dddr';

        if (stripos($this->sql, 'mus')) {
            $field = 'mus.year';
        } else {
            $field = 'yus.year';
        }
        
        $val = $this->getValue();
        $from = $val['from'];
        $to = $val['to']; 

        if ($from['year']==$to['year']) {
            $this->addWhereClause = "($field={$from['year']} AND month BETWEEN {$from['month']} AND {$to['month']})";
        } else {
            $this->addWhereClause = "($field={$from['year']} AND month BETWEEN {$from['month']} AND 12)";
            for ($y=$from['year']; $y<$to['year']; ++$y) {
                $this->addWhereClause .= " OR ($field=$y AND month BETWEEN 1 AND 12)";
            }
            $this->addWhereClause .=  " OR ($field={$to['year']} AND month BETWEEN 1 and {$to['month']})";
        }
    }

    public function getValue() {
        if(!isset($_REQUEST["prm_$this->ID"]))
            return null;

        if ($this->typeCode === 'ms' && (!isset($_REQUEST['useHidden']) || $_REQUEST['useHidden'] == null)) {
            return implode("', '", explode(',', str_replace('\\\\', ',', $_REQUEST["prm_$this->ID"])));
        } else if ($this->typeCode === 'dddr') {
            return $_REQUEST["prm_$this->ID"]; 
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
            ->changeDB(Config::$database->{$parmReport->getDBName()})
            ->query($parmSQL)
            ->fetchRows();
        $num_rows = count($result);
        $valueArray = new SplFixedArray($num_rows);
        for ($i = 0; $i < $num_rows; ++$i) {
            $valueArray[$i] = array('cde' => $result[$i][0],'val' => $result[$i][1]);
        }
        return $valueArray;
    }

    // used only for allowing access to admin page
    public function isParent() {
        // set database to reporting database name
        Config::init();
        $row = $this->db
            ->changeDB(Config::$database->name)
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
            ->changeDB(Config::$database->name)
            ->query("SELECT reportParameterID 
            FROM ReportParameter 
            WHERE parentReportParameterID = '{$this->ID}' ORDER BY 1")
            ->fetchRows(MYSQLI_ASSOC);
        $num_rows = count($result);
        $objects = new SplFixedArray($num_rows);
        for ($i = 0; $i < $num_rows; ++$i) {
            $objects[$i] = new ReportParameter($result[$i]['reportParameterID']);
        }
        return $objects;
    }

    // get the display name of the publisher or platform that was sent in
    public function getPubPlatDisplayName($id){
        // get report info so we can determine which database to use
        $parmReport = new Report($this->reportID);
        Config::init();
        $id = strtoupper($id);
        $sql = "select distinct reportDisplayName from ";
        if (substr($id, 0, 2) === 'PB') {
            $sql .= "PublisherPlatform where concat('PB_', publisherPlatformID)";
        } else {
            $sql .= "Platform where concat('PL_', platformID)";
        }
        $sql .= " in ('$id') order by 1";
        $result = $this->db
            ->changeDB(Config::$database->{$parmReport->getDBName()})
            ->query($sql)
            ->fetchRows(MYSQLI_ASSOC);
        return array_column($result, 'reportDisplayName');
    }
}
?>
