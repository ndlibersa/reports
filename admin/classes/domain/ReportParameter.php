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
            DateRange::Convert($this);
        }
    }

    public function printHTMLdateRangePicker() {
        $vals = $this->getValue();
        if (! $vals) {
            $vals = array('m0'=>1,'y0'=>-1,'m1'=>12,'y1'=>-1);
        }

        $varname_parentID = "prm_$this->parentReportParameterID";
        $parentID = null;
        if (isset($_GET[$varname_parentID])) {
            $parentID = $_GET[$varname_parentID];
        }

        $months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
        $years = $this->getSelectValues($parentID);
        echo "<div id='div_parm_$this->ID' class='param'>
            <input type='hidden' id='daterange' name='prm_$this->ID' />";
        for ($i=0;$i<2;$i++) {
            $mOpts = "";
            $yOpts = "";
            $legendtxt = ($i)?'Through date':'From date';
            for ($mi=1;$mi<count($months);$mi++) {
                $sel = ($vals["m$i"]==$mi)?'selected="selected"':'';
                $mOpts .= "<option value='$mi' $sel>{$months[$mi]}</option>";
            }

            foreach ($years as $y) {
                $sel = ($vals["y$i"]==$y['cde'])?'selected="selected"':'';
                $yOpts .= "<option value=\"{$y['cde']}\" $sel>{$y['val']}</option>";
            }
            echo "<br />
                <fieldset>
                    <legend>$legendtxt:</legend>
                    <select id='date{$i}m' class='opt' onchange='javascript:daterange_onchange($i);'>
                        $mOpts
                    </select>
                    <select id='date{$i}y' class='opt' onchange='javascript:daterange_onchange($i);'>
                        $yOpts
                    </select>
                </fieldset>";
        }
        echo "</div>";
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
        $id = strtoupper($id);
        $sql = "select distinct reportDisplayName from ";
        if (substr($id, 0, 2) === 'PB') {
            $sql .= "PublisherPlatform where concat('PB_', publisherPlatformID)";
        } else {
            $sql .= "Platform where concat('PL_', platformID)";
        }
        $sql .= " in ('$id') order by 1";
        $result = $this->db
            ->selectDB(Config::$database->{$parmReport->dbname})
            ->query($sql)
            ->fetchRows(MYSQLI_ASSOC);
        return array_column($result, 'reportDisplayName');
    }
}
?>
