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

        if (stripos($parm->sql, 'mus')) {
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
            $parm->addWhereClause = "($field={$range['y0']} AND month BETWEEN {$range['m0']} AND {$range['m1']})";
        } else {
            $parm->addWhereClause = "($field={$range['y0']} AND month BETWEEN {$range['m0']} AND 12)";
            for ($y=$range['y0']; $y<$range['y1']; ++$y) {
                $parm->addWhereClause .= " OR ($field=$y AND month BETWEEN 1 AND 12)";
            }
            $parm->addWhereClause .=  " OR ($field={$range['y1']} AND month BETWEEN 1 and {$range['m1']})";
        }

        return $parm;
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
}
