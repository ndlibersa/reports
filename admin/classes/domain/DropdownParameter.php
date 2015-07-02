<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DropdownParameter
 *
 * @author bgarcia
 */
class DropdownParameter extends Parameter implements ParameterInterface {

    public function fetchValue() {
        if(!isset($_REQUEST["prm_$this->id"]))
            return null;
        return trim($_REQUEST["prm_$this->id"]);
    }

    public function htmlForm() {
        $options = "";
        if ($this->requiredInd != '1') {
            $options .= "<option value=''";
            if ($this->value===null)
                $options .= "selected='selected'";
            $options .= ">all</option>";
        }
        $firstrow = true;
        if (isset(Parameter::$ajax_parmValues[$this->parentReportParameterID]))
            $p = Parameter::$ajax_parmValues[$this->parentReportParameterID];
        else
            $p = null;
        foreach ( $this->getSelectValues($p) as $value ) {
            if ($firstrow && ($this->requiredInd == '1'))
                Parameter::$ajax_parmValues[$this->id] = $value[0];
            $options .= "<option value='{$value['cde']}'";
            if ($this->value!==null && $this->value==$value['cde']) {
                $options .= " selected='selected'";
            }
            $options .= ">" . $value['val'] . "</option>";
            $firstrow = false;
        }
        echo "<div id='div_parm_$this->id'>
                      <br />
                      <label for='prm_$this->id'>$this->displayPrompt</label>";
        echo "<select name='prm_$this->id' id='prm_$this->id' class='opt' ";
        if ($this->isParent()) {
            echo "onchange='javascript:updateChildren($this->id);' ";
        }
        echo ">$options</select></div>";
    }

    public function ajax_getChildUpdate() {
        $reportParameterVal = $_GET['reportParameterVal'];
        echo "<select name='prm_$this->id' id='prm_$this->id' class='opt' ";
        // check if it's a parent
        if ($this->isParent()) {
            echo "onchange='javascript:updateChildren($this->id);'>";
        } else {
            echo ">";
        }
        if ($this->requiredInd != '1') {
            echo "<option value='' selected>All</option>";
        }

        $firstrow = true;
        foreach ( $this->getSelectValues($reportParameterVal) as $value ) {
            if ($firstrow && ($this->requiredInd == '1'))
                Parameter::$ajax_parmValues[$this->id] = $value[0];
            echo "<option value='{$value['cde']}'>" . $value['val'] . "</option>";
            $firstrow = false;
        }

        echo "</select>";
    }
}
