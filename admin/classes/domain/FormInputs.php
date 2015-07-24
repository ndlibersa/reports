<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormInputs
 *
 * @author bgarcia
 */
class FormInputs {
    private static $hidden = array();
    private static $visible = array();

    public static function getVisible(){
        if (count(FormInputs::$visible))
            return "?" . http_build_query(self::$visible);
        return "";
    }

    public static function getHidden(){
        if (count(FormInputs::$hidden)>0)
            return "<input type='hidden' name=\"" . implode("\"/><input type='hidden' name=\"",FormInputs::$hidden) . "\"/>";
        return "";
    }

    private static function validate($name,$val) {
        if (!is_string($name) || $name=='') {
            throw new InvalidArgumentException("param 'name' needs to be a non-empty string.");
        } else if (is_array($val)) {
            throw new InvalidArgumentException("param 'val' should not be an array.");
        }
    }

    public static function addVisible($name, $val){
        FormInputs::validate($name, $val);
        FormInputs::$visible[$name] = $val;
    }

    public static function addHidden($name, $val){
        FormInputs::validate($name, $val);
        FormInputs::$hidden[] = "$name\" value=\"$val";
    }
}
