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
    
    public static function GetVisible() {
        $obj = new FormInputs;
        $obj->str = '';
        $obj->isVisible = true;
        return $obj;
    }
    
    public static function GetHidden() {
        $obj = new FormInputs;
        $obj->str = '';
        $obj->isVisible = false;
        return $obj;
    }

	public function getStr(){
		if ($this->isVisible)
			return "?" . ltrim($this->str,"&");
		return $this->str;
	}
    
    public function addParam($name, $val){
        if (!is_string($name) || $name=='') {
            throw new InvalidArgumentException("[param 'name': $name]");
        } else if (is_array($val)) {
            throw new InvalidArgumentException("[param 'val' should not be an array]");
        }   

        if($this->isVisible) {
			$this->str .= "&$name=$val";
        } else {
			$this->str .= "<input type='hidden' name=\"$name\" value=\"$val\">";
        }
		return $this;
    }
}
