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
session_start();
//require 'minify.php';
//ob_start('minify_output');
ob_start();

include_once 'directory.php';


if (isset($_REQUEST['outputType'])) {
    $outputType = $_REQUEST['outputType'];
} else {
    $outputType = 'web';
}

$report = ReportFactory::makeReport($_REQUEST['reportID']);
Parameter::setReport($report);
//FormInputs::init() and ReportNotes::init(..) are called by Report constructor
FormInputs::addHidden('outputType',$outputType);
if (! isset($_REQUEST['reportID'])) {
    error_log("missing reportID; redirecting to index.php");
    header("location: index.php");
    exit();
}
if ($outputType === 'web' && isset($_REQUEST['startPage'])) {
    $startRow = $_REQUEST['startPage'];
} else {
    $startRow = 1;
}
if ($report->titleID) {
    Parameter::$display = '<b>Title:</b> ' . $report->getUsageTitle($report->titleID) . '<br/>';
}

// loop through parameters
foreach ( $report->getParameters() as $parm ) {
    $parm->process();
}
// if titleID was passed in, add that to addwhere
if (($report->id === '1') && ($report->titleID != '')) {
    $report->addWhere[1] .= " AND t.titleID = $report->titleID";
}

$pageTitle = $report->name;





///////////////////////////////////header (start)/////////////////////
if ($outputType === 'print') {
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml'>
        <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title>CORAL Usage Statistics Reporting - ",$report->name,"</title>
        <link rel='stylesheet' href='css/print.css' type='text/css'
        media='screen' />
        </head>
        <body>";

} else if ($outputType === 'web' || $outputType==='pop') {
    include 'templates/header.php';
} else {
    // required to allow downloads in IE 6 and 7
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Transfer-Encoding: binary");
    header("Content-type: application/vnd.ms-excel;");
    header("Content-Disposition: attachment; filename='" . strtr($report->name, ' ', '_') . "'");
    echo "<html><head></head><body>";
}
/////////////////////////////////////header (end)//////////////////





echo "<center>
    <table class='noborder' style='width: 780px;'>
    <tr>
    <td class='noborder' align=center colspan='2'>
    <table class='noborder' style='text-align: left;'>";





////////////////////logo/splash and param list (start)//////////////////
echo "<tr>";
if ($outputType === 'web'){
    echo "<td class='head report-head-img-box' align=left valign='top'><a href='index.php'><img class='report-head-img' src='images/transparent.gif' alt=''/></a></td>
            <td class='noborder report-head-info-box' align=left valign='bottom'>
            <table class='noborder'>
            <tr valign='bottom'>
            <td class='head' style='padding: 5px; vertical-align: bottom;'>
            <form name='viewreport' method='post' action='report.php",FormInputs::getVisible(),"'>",
            FormInputs::getHidden();
    echo "<font size='+1'>",$report->name,"</font>&nbsp;<a href=\"javascript:showPopup('report','",$report->id,"');\"
        title='Click to show information about this report'
        style='border: none'><img src='images/help.gif'
        style='border: none' alt='help'/></a><br/>", Parameter::$display,"<a href=\"index.php",
        FormInputs::getVisible(),"\">Modify Parameters</a>&nbsp; <a href='index.php'>Create New Report</a> <br/>";
    $html = array('xls','print');
    for($i=0;$i<2;$i++){
        echo "<a href=\"javascript:viewReportOutput('{$html[$i]}');\"
            style=\"border: none\"><img border='0'
            src=\"images/",$html[$i], ($i)?'er':'', ".gif\" alt='",$html[$i],
            ($i)?'er':'',"'/></a> ";
    }
    echo "<br/></form></td>
        <td class='head' align=right valign='top'>&nbsp;</td></tr>
        </table></td>";
} else {
    echo "<td class='head'><font size='+1'>",$report->name,"</font><br/>",Parameter::$display,"<br/></td>";
}
echo "</tr>";
////////////////////////logo/splash and param list (end)/////////////////





///////////////////////////report tables (start)///////////////////////
$textAdd = (($report->id === '1') || ($report->id === '2')) ? 'By Month and Resource' : '';
for ($irep=0; $irep<2; $irep++) {
    if ($irep===1)
        $textAdd = "from an Archive $textAdd";
    echo "<tr class='rtitle'><td colspan='2' class='noborder'>Number of Successful Full-Text Article Requests $textAdd</td></tr>
          <tr><td colspan='2' class='noborder'>";
    echo "<table id='R$irep' class='table rep-res'";
    if ($outputType === 'web') {
        echo " style='width: 100%'>";
    } else {
        echo " border='1'>";
    }

    $allowSort = (!$report->onlySummary || $outputType!=='web');
    $reportArray = $report->run($irep===1,$allowSort);






    ////////////////////table header (start)//////////////
    $report->table->displayHeader($outputType,$report->sort);
    //////////////////table header (end)/////////////////





    if ($perform_subtotal_flag = count($report->table->columnData['sum'])>0) {
        $totalSumArray = array();
        foreach ($report->table->fields() as $f) {
            $totalSumArray[$f] = 0;
        }
    }

    // loop through resultset
    $rownum = 0;





    /////////////////////////table body (start)/////////////////////////
    $tblBody = "<tbody>";
    while ($currentRow = $reportArray->fetchRowPersist(MYSQLI_ASSOC) ) {
        if (isset($currentRow['platformID']))
            ReportNotes::addPlatform($currentRow['platformID']);
        if (isset($currentRow['publisherPlatformID']))
            ReportNotes::addPublisher($currentRow['publisherPlatformID']);

        $colnum = 1;
        $subtotal = 0;
        $rowOutput = "<tr class='data'>";
        foreach ( ReportTable::filterRow($currentRow)
            as $field => $value ) {


            if ($perform_subtotal_flag && isset($report->table->columnData['sum'][$field])) {
                // get the numbers out for summing
                if ($field==='QUERY_TOTAL') {
                    $value = $subtotal;
                } else {
                    $subtotal += $value;
                }
                $totalSumArray[$field] += $value;
            }

            $rowOutput .= ReportTable::formatColumn($report,$outputType,$currentRow,$field,$value);

            // end if display columns is Y
            ++$colnum;
        } // end loop through columns
        $rowOutput .= "</tr>";
        ++$rownum;

        if (! $report->onlySummary || $outputType!=='web')
            $tblBody .= $rowOutput;
    }
    $tblBody .= "</tbody>";
    ///////////////////////////table body (end)/////////////////////





    ///////////////////////////table footer (start)/////////////////
    echo "<tfoot>";
    if ($rownum === 0) {
        echo "<tr class='data'><td colspan=" . $report->table->nfields() . "><i>Sorry, no rows were returned.</i></td></tr>";
    } else {
        if (/*$outputType != 'xls' &&*/ $perform_subtotal_flag) {

            $rowparms = array();
            $total = null;
            foreach ($report->table->fields() as $field) {
                if (isset($report->table->columnData['sum'][$field],$totalSumArray[$field])) {
                    $total = $report->table->sumColumn($field, $totalSumArray, $rownum);
                }
                $rowparms[] = ($total===null||$total==='')?'&nbsp;':$total;
                $total = null;
            }

            $rowparms[0] = "Total for Report";
            echo ReportTable::formatTotalsRow($rowparms);
        }

        if (!$report->onlySummary || $outputType!=='web') {
            echo "<tr><td colspan=" . $report->table->nfields() . " align='right'><i>Showing rows ",$startRow," to ";
            if ((ReportTable::$maxRows > 0) && ($rownum > ReportTable::$maxRows)) {
                echo ReportTable::$maxRows . " of " . ReportTable::$maxRows;
            } else {
                echo "$rownum of $rownum";
            }
            echo '</i></td></tr>';
        }
    }
    echo '</tfoot>';
    /////////////////////////table footer (end)////////////////////





    echo $tblBody;

    echo "</table></td></tr>";
}
////////////////////////////report tables (end)///////////////////////





////////////////////////legend (start)//////////////////
$outlier = $report->getOutliers();
echo "<tr><td class='noborder' style='text-align: left;'><br/> <br/>";
// for excel
$outlier_cls = array('flagged','overriden','merged');
$rp_fldcnt = $report->table->nfields();
$modcolcount = $rp_fldcnt - 2;
$nbsp6 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$txt_merged = "Multiple titles with the same print ISSN (generally multiple parts) have been merged together";

if ($outputType != 'xls') {
    echo "<table style='width: 350px; border-width: 1px'>
        <tr><td colspan='2'><b>Key</b></td></tr>";
    if (!$report->showUnadjusted) {
        $outlier_txt = array('not been adjusted','been adjusted manually by Electronic Resources',$txt_merged);

        for ($i=0;$i<3;$i++) {
            echo "<tr>
                <td class='{$outlier_cls[$i]}'>&nbsp;</td>
                <td>Programmatically flagged as outlier based on previous 12
                month average. The number has {$outlier_txt[$i]}.</td>
                </tr>";
        }
    } else {
        for ($i=1;$i<=3;++$i) {
            echo "<tr>
                <td class='l$i'>&nbsp;</td>
                <td>Programmatically flagged as outlier using the following formula: Count is {$outlier[$i]['count']} over {$outlier[$i]['percent']}% of the previous 12 month average. </td>
                </tr>";
        }
        echo "<tr>
            <td class='{$outlier_cls[2]}'>&nbsp;</td>
            <td>$txt_merged.</td>
            </tr>";
    }
    echo "</table>";
    // excel
} else {
    echo "<table style='border-width: 1px'>";
    if (!$report->showUnadjusted) {
        $html = array(
            "Programmatically flagged as outlier based on previous 12 month average. The number has not been adjusted.",
            "Programmatically flagged as outlier based on previous 12 month average. The number has been adjusted manually by Electronic Resources.",
            "Multiple titles with the same print ISSN (generally multiple parts) have been merged together"
        );
        for ($i=0; $i<3; $i++) {
            echo "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr>
                <td class='noborder'";

            if ($i===0) {
                echo " align=right><b>Color Background Key</b>";
            } else {
                echo ">$nbsp6";
            }

            echo "</td><td class='{$outlier_cls[$i]}'>&nbsp;</td>
                <td class='noborder' colspan='$modcolcount'>{$html[$i]}.</td>
                </tr></table></td></tr>";
        }
    } else {
        $html = array();
        for ($i=1;$i<=3;$i++) {
            $html[$i] = array(
                'col'=>Color::$levels[$i][2],
                'cnt'=>$outlier[$i]['count'],
                '%'=>$outlier[$i]['percent']);
        }
        $html_top_opt = array("align=right><b>Color Background Key</b>",">$nbsp6",">$nbsp6");
        for ($i=1;$i<=3;++$i) {
            echo "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr><td class='noborder' {$html_top_opt[$i-1]}</td>
                <td style='width: 20px;' bgcolor='{$html[$i]['col']}'>&nbsp;</td>
                <td class='noborder' colspan='$modcolcount'>Programmatically flagged as outlier using the following formula: Count is {$html[$i]['cnt']} over {$html[$i]['%']}% of the previous 12 month average.</td></tr></table></td></tr>";
        }
        echo "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr><td class='noborder'>$nbsp6</td>
            <td class='{$outlier_cls[2]}'>&nbsp;</td>
            <td class='noborder' colspan='$modcolcount'>$txt_merged.</td></tr></table></td></tr>";
    }
}
//////////////////////////legend (end)///////////////////

/* display any publisher or platform notes */
echo "<tr><td class='noborder' style='text-align: left;'>";
ReportNotes::displayNotes();
echo "</td></tr>

    </table><br/>
    </td>
    <td class='noborder'>&nbsp;</td>
    </tr>
    </table>
    </center>";


////////////////////footer//////////////////
echo "<script type='text/javascript' src='js/report.js'></script>";

if ($outputType === 'print') {?>
<script type="text/javascript">
    <!--
    window.print();
//-->
</script>
<?php
}

include 'templates/footer.php';
///////////////////footer (end)///////////////


ob_end_flush();
