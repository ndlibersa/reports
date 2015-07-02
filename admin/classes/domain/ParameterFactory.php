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
    public static function makeParam($reportParameterID) {
        $parm = null;
        $db = new DBService();
        $result = $db
            ->query("SELECT *
            FROM ReportParameter
            WHERE reportParameterID = '$reportParameterID' LIMIT 1")
            ->fetchRow(MYSQLI_ASSOC);

        if($result['parameterTypeCode']==='chk'){
            $parm = new CheckParameter;
        } else if ($result['parameterTypeCode']==='dd') {
            if ($result['parameterAddWhereClause'] === 'limit') {
                $parm = new LimitParameter;
            } else if ($result['reportID']!=5&& $result['reportID']!=6 // report#5 and report#6 don't finish when date ranges are enabled
                && $result['parameterDisplayPrompt'] === 'Year') {
                $parm = new DateRangeParameter;
            } else if ($result['parameterDisplayPrompt'] === 'Provider / Publisher'
                || $result['parameterDisplayPrompt'] === 'Provider'
                || $result['parameterDisplayPrompt'] === 'Publisher') {
                $parm = new ProviderPublisherDropdownParameter;
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
        } else {
            $parm = new Parameter();
        }

        $parm->db = $db;
        $parm->id = $reportParameterID;
        $parm->reportID = $result['reportID'];
        $parm->displayPrompt = $result['parameterDisplayPrompt'];
        $parm->addWhereClause = $result['parameterAddWhereClause'];
        $parm->typeCode = $result['parameterTypeCode'];
        $parm->formatCode = $result['parameterFormatCode'];
        $parm->requiredInd = $result['requiredInd'];
        $parm->addWhereNum = $result['parameterAddWhereNumber'];
        $parm->sql = $result['parameterSQLStatement'];
        $parm->parentReportParameterID = $result['parentReportParameterID'];
        $parm->sqlRestriction = $result['parameterSQLRestriction'];

        $parm->value = $parm->fetchValue();
        if ( is_a($parm, 'DateRangeParameter')) {
            $parm->init();
        }
        return $parm;
    }
}
