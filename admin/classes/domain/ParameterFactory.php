<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportParameterFactory
 *
 * @author bgarcia
 */
class ParameterFactory {
    public static function makeParam($reportID,$reportParameterID) {
        $parm = null;
        $db = new DBService();
        $result = $db
            ->query("SELECT rp.*, rpm.parentReportParameterID
            FROM ReportParameter rp, ReportParameterMap rpm
            WHERE rp.reportParameterID = '$reportParameterID' LIMIT 1")
            ->fetchRow(MYSQLI_ASSOC);

        if ($result['parameterDisplayPrompt'] === 'Provider / Publisher') {
                $parm = new ProviderPublisherParameter;
        } else if ($result['parameterDisplayPrompt'] === 'Provider') {
            $parm = new ProviderParameter;
        }else if ($result['parameterDisplayPrompt'] === 'Publisher') {
            $parm = new PublisherParameter;
        } else if($result['parameterTypeCode']==='chk'){
            if ($result['parameterDisplayPrompt']==="Do not adjust numbers for use violations") {
                $parm = new CheckUnadjustedParameter;
            } else {
                $parm = new CheckboxParameter;
            }
        } else if ($result['parameterTypeCode']==='dd') {
            if ($result['parameterAddWhereClause'] === 'limit') {
                $parm = new LimitParameter;
            } else if ($result['parameterDisplayPrompt'] === 'Year') {
                $parm = new YearParameter;
            } else if ($result['parameterDisplayPrompt'] === 'Date Range') {
                $parm = new DateRangeParameter;
            } else {
                $parm = new DropdownParameter;
            }
        } else if ($result['parameterTypeCode']==='ms') {
            if ($result['parameterDisplayPrompt'] === 'Provider / Publisher'
                || $result['parameterDisplayPrompt'] === 'Provider'
                || $result['parameterDisplayPrompt'] === 'Publisher') {
                $parm = new ProviderPublisherDropdownParameter;
            } else {
                $parm = new MultiselectParameter;
            }
        } else if ($result['parameterTypeCode']==='txt') {
            $parm = new TextParameter();
        } else {
            $parm = new Parameter();
        }

        $parm->db = $db;
        $parm->id = $reportParameterID;
        $parm->reportID = $reportID;
        $parm->prompt = $result['parameterDisplayPrompt'];
        $parm->addWhereClause = $result['parameterAddWhereClause'];
        $parm->typeCode = $result['parameterTypeCode'];
        $parm->requiredInd = $result['requiredInd']===1;
        $parm->addWhereNum = $result['parameterAddWhereNumber'];
        $parm->sql = $result['parameterSQLStatement'];
        $parm->parentReportParameterID = $result['parentReportParameterID'];
        $parm->sqlRestriction = $result['parameterSQLRestriction'];

        $parm->value = $parm->value();
        if ( is_a($parm, 'DateRangeParameter')) {
            $parm->init();
        }
        return $parm;
    }
}
