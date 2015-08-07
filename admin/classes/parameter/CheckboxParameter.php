<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckboxParameter
 *
 * @author bgarcia
 */
class CheckboxParameter extends Parameter implements ParameterInterface {

    public function process() {
        if ($this->value === 'on' || $this->value === 'Y') {
            FormInputs::addVisible("prm_$this->id", 'Y');
            Parameter::$display .= $this->description();
            if ($flag = $this->flagName())
                Parameter::$report->{$flag} = true;
        }
    }

    //this dummy implementation is preferrable to an abstract class
    protected function flagName() {return null;}

    public function value() {
        if(!isset($_REQUEST["prm_$this->id"]))
            return null;
        return trim($_REQUEST["prm_$this->id"]);
    }

    public function form() {
        if(isset($_REQUEST["prm_$this->id"])) {
                    $this->value = 'checked';
        }
        echo "<div id='div_parm_$this->id'>
                      <br />
                      <label for='prm_$this->id'>$this->prompt</label>
                      <input type='checkbox' name='prm_$this->id' class='opt'
            style='text-align:left;width:13px;' $this->value/>
                  </div>";
    }
}
