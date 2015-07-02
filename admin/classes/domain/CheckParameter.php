<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckParameter
 *
 * @author bgarcia
 */
class CheckParameter extends Parameter implements ParameterInterface {

    public function fetchValue() {
        if(!isset($_REQUEST["prm_$this->id"]))
            return null;
        return trim($_REQUEST["prm_$this->id"]);
    }

    public function process() {
        if (($this->value === 'on') || ($this->value === 'Y')) {
            Parameter::$report->showUnadjusted = true;
            FormInputs::addVisible("prm_$this->id", 'Y');
            Parameter::$display .= $this->htmlDisplay();
        }
    }

    public function htmlDisplay() {
        return '<b>Numbers are not adjusted for use violations</b><br/>';
    }

    public function htmlForm() {
        if(isset($_REQUEST["prm_$this->id"])) {
                    $this->value = 'checked';
        }
        echo "<div id='div_parm_$this->id'>
                      <br />
                      <label for='prm_$this->id'>$this->displayPrompt</label>
                      <input type='checkbox' name='prm_$this->id' class='opt'
            style='text-align:left;width:13px;' $this->value/>
                  </div>";
    }
}
