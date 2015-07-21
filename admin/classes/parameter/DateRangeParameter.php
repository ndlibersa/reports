<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DateRangeParameter
 *
 * @author bgarcia
 */
class DateRangeParameter extends DropdownParameter implements ParameterInterface {

    public function value() {
        if(! isset($_REQUEST["prm_$this->id"])) {
            $years = $this->getSelectValues($this->parentReportParameterID);
            $y0 = min($years);
            $y1 = max($years);
            return array('m0'=>1,'y0'=>$y0['val'],'m1'=>12,'y1'=>$y1['val']);
        }
        return $this->decode($_REQUEST["prm_$this->id"]);
    }

    public function process() {
        if ($this->value !== null) {
            Parameter::$report->addWhere[$this->addWhereNum] .= " AND $this->addWhereClause";
            Parameter::$report->applyDateRange($this->value);
            FormInputs::addVisible("prm_$this->id",$this->encode());
            $this->value = $this->value['m0'] . '/' . $this->value['y0'] . '-'
                . $this->value['m1'] . '/' . $this->value['y1'];
            Parameter::$display .= $this->description();
        }
    }

    public function description() {
        return "<b>{$this->prompt}:</b> '$this->value'<br/>";
    }

    public function init() {
        if ($this->value['y0']===$this->value['y1']) {
            $this->addWhereClause = "(mus.year={$this->value['y0']} AND "
            . DateRangeParameter::monthRangeSameYearSQL($this->value['m0'], $this->value['m1']) . ")";
        } else {
            $this->addWhereClause = "((mus.year={$this->value['y0']} AND month BETWEEN {$this->value['m0']} AND 12)";
            for ($y=$this->value['y0']+1; $y<$this->value['y1']; ++$y) {
                $this->addWhereClause .= " OR mus.year=$y";
            }
            $this->addWhereClause .=  " OR (mus.year={$this->value['y1']} AND "
            . DateRangeParameter::monthRangeSameYearSQL(1, $this->value['m1']) . "))";
        }
    }

    private static function monthRangeSameYearSQL($start,$end) {
        if ($start===$end)
            return "month=$start";
        return "month BETWEEN $start AND $end";
    }

    public function encode() { // for HTTP GET
        if(!isset($this->value['y0'],$this->value['y1'],$this->value['m0'],$this->value['m1'])) {
            throw new InvalidArgumentException("missing one or more array fields");
        }
        $str = sprintf('%02u%04u%02u%04u',$this->value['m0'],$this->value['y0'],$this->value['m1'],$this->value['y1']);
        if($str==null || $str=='' || strlen($str)!==12) {
            throw new UnexpectedValueException("encoding failed: $str");
        }
        return $str;
    }

    public function decode($val) { // for HTTP GET
        if (!is_string($val)) {
            throw new UnexpectedValueException("passed value is wrong type, expected: string");
        } else if (strlen($val)!==12) {
            throw new UnexpectedValueException("decoding failed: $val");
        }
        $parsed = sscanf($val,'%02u%04u%02u%04u');
        $range = array('m0'=>$parsed[0], 'y0'=>$parsed[1],'m1'=>$parsed[2], 'y1'=>$parsed[3]);
        if($range==null || !is_array($range)){
            throw new UnexpectedValueException("return value is wrong type, expected: array");
        }
        return $range;
    }

	public function form() {

        $varname_parentID = "prm_$this->parentReportParameterID";
        $parentID = null;
        if (isset($_GET[$varname_parentID])) {
            $parentID = $_GET[$varname_parentID];
        }

        $months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
        $years = $this->getSelectValues($parentID);
        echo "<div id='div_parm_$this->id' class='param'>
            <input type='hidden' id='daterange' name='prm_$this->id' />";
        for ($i=0;$i<2;$i++) {
            $mOpts = "";
            $yOpts = "";
            $legendtxt = ($i)?'Through date':'From date';
            for ($mi=1;$mi<count($months);$mi++) {
                $sel = ($this->value["m$i"]==$mi)?'selected="selected"':'';
                $mOpts .= "<option value='$mi' $sel>{$months[$mi]}</option>";
            }

            foreach ($years as $y) {
                $sel = ($this->value["y$i"]==$y['cde'])?'selected="selected"':'';
                $yOpts .= "<option value=\"{$y['cde']}\" $sel>{$y['val']}</option>";
            }
            echo "<br />
                <fieldset>
                    <legend>$legendtxt:</legend>
                    <select id='date{$i}m' class='opt'>
                        $mOpts
                    </select>
                    <select id='date{$i}y' class='opt'>
                        $yOpts
                    </select>
                </fieldset>";
        }
        echo "</div>";
    }

    public function ajax_getChildUpdate() {

        $varname_parentID = "prm_$this->parentReportParameterID";
        $parentID = 0;
        if (isset($_GET[$varname_parentID])) {
            $parentID = $_GET[$varname_parentID];
        }

        $months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
        $years = $this->getSelectValues($parentID);
        echo "<div id='div_parm_$this->id' class='param'>
            <input type='hidden' id='daterange' name='prm_$this->id' />";
        for ($i=0;$i<2;$i++) {
            $mOpts = "";
            $yOpts = "";
            $legendtxt = ($i)?'Through date':'From date';
            for ($mi=1;$mi<count($months);$mi++) {
                $sel = ($this->value["m$i"]==$mi)?'selected="selected"':'';
                $mOpts .= "<option value='$mi' $sel>{$months[$mi]}</option>";
            }

            foreach ($years as $y) {
                $sel = ($this->value["y$i"]==$y['cde'])?'selected="selected"':'';
                $yOpts .= "<option value=\"{$y['cde']}\" $sel>{$y['val']}</option>";
            }
            echo "<br />
                <fieldset>
                    <legend>$legendtxt:</legend>
                    <select id='date{$i}m' class='opt'>
                        $mOpts
                    </select>
                    <select id='date{$i}y' class='opt'>
                        $yOpts
                    </select>
                </fieldset>";
        }
        echo "</div>";
    }

    public static function getMonthsUsed(array $range) {
        $used = array();

        $miny = intval($range['y0']);
        $maxy = intval($range['y1']);

        for ($y=$miny; $y<=$maxy; ++$y) {
            if (count($used)===12) {
                return $used;
            }

            $minm = intval(($y===$miny)? $range['m0'] : 1); //-1 means JAN=0
            $maxm = intval(($y===$maxy)? $range['m1'] : 12); //-1 means JAN=0

            for ($m=$minm; $m<=$maxm; ++$m) {
                $used[$m] = true;
                if (count($used)===12) {
                    return $used;
                }
            }
        }
        return $used;
    }
}
