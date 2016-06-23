<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LimitParameter
 *
 * @author bgarcia
 */
class LimitParameter extends DropdownParameter implements ParameterInterface {

    public function process() {
        if ($this->value !== null) {
            Parameter::$report->addWhere[0] = ''; // changed from $add_where. Assumed mistake.
            ReportTable::$maxRows = $this->value;
            FormInputs::addVisible("prm_$this->id",$this->value);
            Parameter::$display .= $this->description();
        }
    }

    public function description() {
        return "<b>Limit:</b> Top $this->value<br/>";
    }
}
