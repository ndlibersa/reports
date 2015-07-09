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

    public function value() {
        if(!isset($_REQUEST["prm_$this->id"]))
            return null;

        $val = trim($_REQUEST["prm_$this->id"]);
        if ($val==='')
            return null;

        return $val;
    }

    public function process() {
        if ($this->value !== null) {
            Parameter::$report->addWhere[$this->addWhereNum] .= " AND " . preg_replace('/PARM/',$this->value,$this->addWhereClause);
            FormInputs::addVisible("prm_$this->id", $this->value);
            Parameter::$display .= $this->description();
        } else if (!$this->requiredInd) {
            Parameter::$display .= "<b>{$this->prompt}:</b> all<br/>";
        }
    }

    public function form() {
        $options = "";
        if (!$this->requiredInd) {
            $options .= "<option value=''";
            if ($this->value===null || $this->value==='')
                $options .= "selected='selected'";
            $options .= ">all</option>";
        }
        $firstrow = true;
        if (isset(Parameter::$ajax_parmValues[$this->parentReportParameterID]))
            $p = Parameter::$ajax_parmValues[$this->parentReportParameterID];
        else
            $p = 0;
        foreach ( $this->getSelectValues($p) as $value ) {
            if ($firstrow && $this->requiredInd)
                Parameter::$ajax_parmValues[$this->id] = $value;
            $options .= "<option value='{$value['cde']}'";
            if ($this->value!==null && $this->value==$value['cde']) {
                $options .= " selected='selected'";
            }
            $options .= ">" . $value['val'] . "</option>";
            $firstrow = false;
        }
        echo "<div id='div_parm_$this->id'>
              <br />
              <label for='prm_$this->id'>$this->prompt</label>";
        echo "<select name='prm_$this->id' id='prm_$this->id' class='opt' ";
        if ($this->isParent()) {
            echo "onchange='javascript:updateChildren($this->reportID,$this->id);' ";
        }
        echo ">$options</select></div>";
    }

    public function ajax_getChildUpdate() {
        $reportParameterVal = $_GET['reportParameterVal'];

        echo "<div id='div_parm_$this->id'>
              <br />
              <label for='prm_$this->id'>$this->prompt</label>";
        echo "<select name='prm_$this->id' id='prm_$this->id' class='opt' ";
        if ($this->isParent()) {
            echo "onchange='javascript:updateChildren($this->reportID,$this->id);'>";
        } else {
            echo ">";
        }
        if (!$this->requiredInd) {
            echo "<option value='' selected>All</option>";
        }

        $firstrow = true;
        foreach ( $this->getSelectValues($reportParameterVal) as $value ) {
            if ($firstrow && $this->requiredInd)
                Parameter::$ajax_parmValues[$this->id] = $value;
            echo "<option value='{$value['cde']}'>" . $value['val'] . "</option>";
            $firstrow = false;
        }

        echo "</select></div>";
    }
}
