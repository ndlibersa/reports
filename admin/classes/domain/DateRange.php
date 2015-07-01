<?php

/**
 **  A specialized class for 'Year' ReportParameters that need to be
 **  handled differently than is specified in the database.
 **
 **/
class DateRange {
    public static function Convert(ReportParameter $parm) {
        $parm->requiredInd = 1;
        $parm->displayPrompt = "Date Range";
        $parm->typeCode = 'dddr';

        if (stripos($parm->addWhereClause, 'mus')!==FALSE) {
            $field = 'mus.year';
        } else {
            $field = 'yus.year';
        }

        if (isset($_REQUEST["prm_$parm->ID"]) && $_REQUEST["prm_$parm->ID"]!='') {
            $range = $parm->getValue();
        } else {
            $range = array('m0'=>1,'y0'=>-1,'m1'=>12,'y1'=>-1);
        }

        if ($range['y0']===$range['y1']) {
            $parm->addWhereClause = "($field={$range['y0']} AND "
            . self::monthRangeSameYearSQL($range['m0'], $range['m1']) . ")";
        } else {
            $parm->addWhereClause = "(($field={$range['y0']} AND month BETWEEN {$range['m0']} AND 12)";
            for ($y=$range['y0']+1; $y<$range['y1']; ++$y) {
                $parm->addWhereClause .= " OR ($field=$y AND month BETWEEN 1 AND 12)";
            }
            $parm->addWhereClause .=  " OR ($field={$range['y1']} AND "
            . self::monthRangeSameYearSQL(1, $range['m1']) . "))";
        }

        return $parm;
    }

    private static function monthRangeSameYearSQL($start,$end) {
        if ($start===$end)
            return "month=$start";
        return "month BETWEEN $start AND $end";
    }

    public static function Encode(array $range) {
        if(!isset($range['y0'],$range['y1'],$range['m0'],$range['m1'])) {
            throw new InvalidArgumentException("missing one or more array fields");
        }
        $str = sprintf('%02u%04u%02u%04u',$range['m0'],$range['y0'],$range['m1'],$range['y1']);
        if($str==null || $str=='' || strlen($str)!==12) {
            throw new UnexpectedValueException("encoding failed: $str");
        }
        return $str;
    }

    public static function Decode($val) {
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

	public static function PrintForm(ReportParameter $param) {
        $vals = $param->getValue();
        if (! $vals) {
            $vals = array('m0'=>1,'y0'=>-1,'m1'=>12,'y1'=>-1);
        }

        $varname_parentID = "prm_$param->parentReportParameterID";
        $parentID = null;
        if (isset($_GET[$varname_parentID])) {
            $parentID = $_GET[$varname_parentID];
        }

        $months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
        $years = $param->getSelectValues($parentID);
        echo "<div id='div_parm_$param->ID' class='param'>
            <input type='hidden' id='daterange' name='prm_$param->ID' />";
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
