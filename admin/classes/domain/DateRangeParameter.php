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

    public function fetchValue() {
        if(! isset($_REQUEST["prm_$this->id"])) {
            return array('m0'=>1,'y0'=>-1,'m1'=>12,'y1'=>-1);
        }
        $val = $_REQUEST["prm_$this->id"];

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

    public function process() {
        if ($this->value !== null) {
            $months = array(
                'JAN','FEB','MAR','APR','MAY','JUN',
                'JUL','AUG','SEP','OCT','NOV','DEC'
                );
            $monthsUsed = DateRangeParameter::getMonthsUsed($this->value);

            $addWhereNum = intval($this->addWhereNum == 2);
            Parameter::$report->addWhere[$addWhereNum] .= " AND $this->addWhereClause";

            for ($i=0; $i<12; ++$i) {
                if (!isset($monthsUsed[$months[$i]])) {
                    Parameter::$report->dropMonths[] = $months[$i];
                }
            }

            FormInputs::addVisible("prm_$this->id",$this->encode());
            $this->value = $this->value['m0'] . '/' . $this->value['y0'] . '-'
                . $this->value['m1'] . '/' . $this->value['y1'];
            Parameter::$display .= $this->htmlDisplay();
        }
    }

    public function htmlDisplay() {
        return "<b>{$this->displayPrompt}:</b> '$this->value'<br/>";
    }

    public function init() {
        $this->requiredInd = 1;
        $this->displayPrompt = "Date Range";

        if (stripos($this->addWhereClause, 'mus')!==FALSE) {
            $field = 'mus.year';
        } else {
            $field = 'yus.year';
        }

        if ($this->value['y0']===$this->value['y1']) {
            $this->addWhereClause = "($field={$this->value['y0']} AND "
            . DateRangeParameter::monthRangeSameYearSQL($this->value['m0'], $this->value['m1']) . ")";
        } else {
            $this->addWhereClause = "(($field={$this->value['y0']} AND month BETWEEN {$this->value['m0']} AND 12)";
            for ($y=$this->value['y0']+1; $y<$this->value['y1']; ++$y) {
                $this->addWhereClause .= " OR ($field=$y AND month BETWEEN 1 AND 12)";
            }
            $this->addWhereClause .=  " OR ($field={$this->value['y1']} AND "
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

	public function htmlForm() {

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

    public static function getMonthsUsed($range) {
        $used = array();

        $months = array(
            'JAN','FEB','MAR','APR','MAY','JUN',
            'JUL','AUG','SEP','OCT','NOV','DEC'
            );

        $miny = intval($range['y0']);
        $maxy = intval($range['y1']);

        for ($y=$miny; $y<=$maxy; ++$y) {
            if (count($used)===12) {
                return $used;
            }

            $minm = intval(($y===$miny)? $range['m0'] : 1)-1; //-1 so JAN=0
            $maxm = intval(($y===$maxy)? $range['m1'] : 12)-1; //-1 so JAN=0

            for ($m=$minm; $m<=$maxm; ++$m) {
                $used[$months[$m]] = true;
                if (count($used)===12) {
                    return $used;
                }
            }
        }
        return $used;
    }
}
