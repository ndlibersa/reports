<?php

/*
 * *************************************************************************************************************************
 * * CORAL Usage Statistics Reporting Module v. 1.0
 * *
 * * Copyright (c) 2010 University of Notre Dame
 * *
 * * This file is part of CORAL.
 * *
 * * CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * *
 * * CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * *
 * * You should have received a copy of the GNU General Public License along with CORAL. If not, see <http://www.gnu.org/licenses/>.
 * *
 * *************************************************************************************************************************
 */

require 'minify.php';
ob_start('minify_output');
include_once 'directory.php';
$action = $_GET['action'];

if ($action === 'getReportParameters') {
    $report = ReportFactory::makeReport($_GET['reportID']);

    // get parameters
    Parameter::$ajax_parmValues = array();

    foreach ( $report->getParameters() as $parm ) {
        $parm->form();
    }
} else if ($action === 'getChildParameters') {
    $parm = ParameterFactory::makeParam($_GET['reportID'],$_GET['parentReportParameterID']);
    $parm->ajaxGetChildParameters();
} else if ($action === 'getChildUpdate') {
    $parm = ParameterFactory::makeParam($_GET['reportID'],$_GET['reportParameterID']);
    $parm->ajaxGetChildUpdate();
} else {
    echo "Action $action not set up!";
}

ob_end_flush();
?>

