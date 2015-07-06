<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CheckSummaryOnlyParameter
 *
 * @author bgarcia
 */
class CheckSummaryOnlyParameter extends CheckboxParameter implements ParameterInterface {
    public function description() {
        return '<b>Not displaying report tables for Web interface</b><br/>';
    }

    protected function flagName() {
        return "onlySummary";
    }

    //this parameter type does not come from the database
    public function __construct($reportID) {
        $this->id = "NoBody";
        $this->reportID = $reportID;
        $this->prompt = "Only show summaries";
        $this->addWhereClause = "";
        $this->typeCode = "chk";
        $this->requiredInd = false;
        $this->addWhereNum = 0;
        $this->sql = "";
        $this->parentReportParameterID = 0;
        $this->sqlRestriction = "";

        $this->value = $this->value();
    }
}
