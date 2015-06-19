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
    protected $str;
    protected $isVisible;

    public static $hidden;
    public static $visible;

    public static function init() {
        self::$hidden = new self;
        self::$hidden->str = "";
        self::$hidden->visible = false;

        self::$visible = new self;
        self::$visible->str = "";
        self::$visible->visible = true;
    }

    public function getStr(){
        if ($this->visible)
            return "?" . ltrim($this->str,"&");
        return $this->str;
    }

    public function addParam($name, $val){
        if (!is_string($name) || $name=='') {
            throw new InvalidArgumentException("param 'name' needs to be a non-empty string.");
        } else if (is_array($val)) {
            throw new InvalidArgumentException("[param 'val' should not be an array]");
        }

        if($this->visible) {
            $this->str .= "&$name=$val";
        } else {
            $this->str .= "<input type='hidden' name=\"$name\" value=\"$val\"/>";
        }
        return $this;
    }
}
