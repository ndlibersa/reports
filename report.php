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
require 'minify.php';
ob_start('minify_output');

include_once 'directory.php';

$reportHelper = new ReportHelper();
$report = $reportHelper->report;
$notes = $reportHelper->notes;
$outlier_cls = array('flagged','overriden','merged');
$pageTitle = $report->name;

if ($reportHelper->outputType === 'print') {
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml'>
        <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title>CORAL Usage Statistics Reporting - $report->name</title>
        <link rel='stylesheet' href='css/print.css' type='text/css'
        media='screen' />
        </head>
        <body>";

} else if ($reportHelper->outputType === 'web' || $reportHelper->outputType==='pop') {
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

echo "<center>
    <table class='noborder' style='width: 780px;'>
    <tr>
    <td class='noborder' align=center colspan='2'>
    <table class='noborder' style='text-align: left;'>
    <tr>";
if ($reportHelper->outputType === 'web'){
    echo "<td class='head report-head-img-box' align=left valign='top'><a
        href='index.php'><img
        class='report-head-img'
            src='images/transparent.gif'></a></td>
            <td class='noborder report-head-info-box' align=left valign='bottom'>
            <table class='noborder'>
            <tr valign='bottom'><td class='head' style='padding: 5px; vertical-align: bottom;'>
            <form name='viewreport' method='post' action='report.php",$reportHelper->visible_inputs->getStr(),"'>",
            $reportHelper->hidden_inputs->getStr();
    echo "<font size='+1'>$report->name</font>&nbsp;<a href=\"javascript:showPopup('report','$report->id');\"
        title='Click to show information about this report'
        style='border: none'><img src='images/help.gif'
        style='border: none'></a><br>
        $reportHelper->paramDisplay<a href='index.php",$reportHelper->visible_inputs->getStr(),"'>Modify Parameters</a>&nbsp; <a href='index.php'>Create New Report</a> <br>";
    $html = array('xls','print');
    for($i=0;$i<2;$i++){
        echo "<a href=\"javascript:viewReportOutput('{$html[$i]}');\"
            style=\"border: none\"><img border='0'
            src=\"images/{$html[$i]}" . (($i)?'er':'') . ".gif\"></a> ";
    }
    echo "<br></form></td>
        <td class='head' align=right valign='top'>&nbsp;</td></tr>
        </table>";
} else {
    echo "<td class='head'><font size='+1'>$report->name</font><br>$reportHelper->paramDisplay<br>";
}
echo "</td></tr>";

$textAdd = (($report->id === '1') || ($report->id === '2')) ? 'By Month and Resource' : '';
$r1_title = "Number of Successful Full-Text Article Requests $textAdd";
$r1_tbl = "table id='R1' class='table rep-res'";
if ($reportHelper->outputType === 'web') {
    echo "
        <tr class='rtitle'><td colspan='2'>
        $r1_title
        </td></tr>
        <tr><td colspan='2' class='shadednoborder'><$r1_tbl style='width: 100%'>";
} else {
    echo "
        <tr><td colspan='2' align=left class='noborder'>
        <font size='+1'>$r1_title</font>
        </td></tr>
        <tr><td colspan='2' align=center class='noborder'><$r1_tbl border='1'>";
}
$reportArray = $reportHelper->getReportResults(false);
echo $reportHelper->process($reportArray,$notes),"</table></td></tr>";

$reportArray = $reportHelper->getReportResults(true); // archive query

if ($reportArray) {
    $r2_title = "Number of Successful Full-Text Article Requests from an Archive $textAdd";
    $r2_tbl = "table id='R2' class='table rep-res'";
    if ($reportHelper->outputType === 'web') {
        echo "<tr class='rtitle'><td colspan='2'>
            $r2_title
            </td></tr>
            <tr><td colspan='2' class='shadednoborder'><$r2_tbl style='width: 100%'>";
    } else {
        for($i=0;$i<2;$i++) {
            echo "<tr><td colspan='2' align=left class='noborder'>&nbsp;</td></tr>";
        }
        echo "<tr><td colspan='2' align=left class='noborder'>
            <font size='+1'>$r2_title</font>
            </td></tr>
            <tr><td colspan='2' align=center class='noborder'><$r2_tbl border='1'>";
    }
    echo $reportHelper->process($reportArray, $notes),"</table></td></tr>";
}
echo "</table>
    </td>
    </tr>
    <tr>
    <td class='noborder' style='text-align: left;'><br> <br>";


// for excel
$modcolcount = $reportHelper->table->nfields() - 2;
$nbsp6 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$txt_merged = "Multiple titles with the same print ISSN (generally multiple parts) have been merged together";
if ($reportHelper->outputType != 'xls') {
    echo "<table style='width: 350px; border-width: 1px'>
        <tr>
        <td colspan='2'><b>Key</b></td>
        </tr>";
    if (!$reportHelper->showUnadjusted) {
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
                <td>Programmatically flagged as outlier using the following formula: Count is {$reportHelper->outlier[$i]['overageCount']} over {$reportHelper->outlier[$i]['overagePercent']}% of the previous 12 month average. </td>
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
    $html = array('ab'=>"Programmatically flagged as outlier based on previous 12 month average. The number has",
        'c'=>"Multiple titles with the same print ISSN (generally multiple parts) have been merged together");

    echo "<table style='border-width: 1px'>";
    if (!$reportHelper->showUnadjusted) {
        echo "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr>
            <td class='noborder' align=right><b>Color Background Key</b></td>
            <td class='{$outlier_cls[0]}'>&nbsp;</td>
            <td class='noborder' colspan='$modcolcount'>{$html['ab']} not been adjusted.</td>
            </tr></table></td></tr>";
        echo "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr>
            <td class='noborder'>$nbsp6</td>
            <td class='{$outlier_cls[1]}'>&nbsp;</td>
            <td class='noborder' colspan='$modcolcount'>{$html['ab']} been adjusted manually by Electronic Resources.</td>
            </tr></table></td></tr>";

        echo "                        <tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr>
            <td class='noborder'>$nbsp6</td>
            <td class='{$outlier_cls[2]}'>&nbsp;</td>
            <td class='noborder' colspan='$modcolcount'>{$html['c']}.</td>
            </tr></table></td></tr>";
    } else {
        $txt_label = "Programmatically flagged as outlier using the following formula: Count is";
        $html = array();
        for ($i=1;$i<=3;$i++) {
            $arr = $reportHelper->outlier[$i];
            $col = Color::$levelColors[$i];
            $html[$i] = array('col'=>$col[2],'cnt'=>$arr['overageCount'],'%'=>$arr['overagePercent']);
        }


        $html_top = "<tr><td colspan='$rp_fldcnt'><table style='border: 0px;'><tr>";
        $html_bot = "</tr></table></td></tr>";

        $html_top_opt = array("align=right><b>Color Background Key</b>",">$nbsp6",">$nbsp6");
        for ($i=1;$i<=3;++$i) {
            echo "$html_top<td class='noborder' {$html_top_opt[$i-1]}</td>
                <td style='width: 20px;' bgcolor='{$html[$i]['col']}'>&nbsp;</td>
                <td class='noborder' colspan='$modcolcount'>$txt_label {$html[$i]['cnt']} over {$html[$i]['%']}% of the previous 12 month average.</td>$html_bot";
        }

        echo $html_top,
            "<td class='noborder'>$nbsp6</td>
            <td class='{$outlier_cls[2]}'>&nbsp;</td>
            <td class='noborder' colspan='$modcolcount'>$txt_merged.</td>",
            $html_bot;




    }
}
unset($reportHelper->showUnadjusted, $reportHelper->outlier);
echo "<tr><td class='noborder' style='text-align: left;'>";

if ($notes->hasPlatforms() || $notes->hasPublishers()) {
    $note_type = array('Platform Interface','Publisher');
    for ($i=0;$i<2;$i++) {
        echo "<br> <br>
            <table style='border-width: 1px'>
            <tr><td colspan='3'><b>{$note_type[$i]} Notes (if available)</b></td></tr>";
        if (!($i) && $notes->hasPlatforms()) {
            foreach ( $notes->platformNotes() as $platform ){
                echo "<tr valign='top'><td align='right'><b>{$platform['reportDisplayName']}</b></td><td>Year";
                if ($platform['startYear'] != '' && ($platform['endYear'] == '' || $platform['endYear'] == '0')){
                    echo ": {$platform['startYear']} to present";
                }else{
                    echo "s: {$platform['startYear']} to {$platform['endYear']}";
                }
                echo '</td><td>This Interface ';
                if ($platform['counterCompliantInd'] == '1'){
                    echo 'provides COUNTER compliant stats.<br>';
                }else{
                    echo 'does not provide COUNTER compliant stats.<br>';
                }
                if ($platform['noteText']){
                    echo "<br><i>Interface Notes</i>: {$platform['noteText']}<br>";
                }
                echo '</td></tr>';
            }
        } else if ($notes->hasPublishers()) {
            foreach ( $notes->publisherNotes() as $publisher ){
                echo "<tr valign='top'><td align='right'><b>{$publisher['reportDisplayName']}</b></td><td>Year";
                if (($publisher['startYear'] != '') && ($publisher['endYear'] == '')){
                    echo ": {$publisher['startYear']}";
                }else{
                    echo "s: {$publisher['startYear']} to {$publisher['endYear']}";
                }
                echo '</td><td>';
                if (isset($publisher['notes'])){
                    echo $publisher['notes'];
                }
                echo '</td></tr>';
            }
        }
        echo '</table>';
    }
}
echo "</td>
    </tr>
    </table> <br></td>
    <td class='noborder'>&nbsp;</td>
    </tr>
    </table>
    </center>
    <script type='text/javascript' src='js/report.js'></script>";

if ($reportHelper->outputType === 'print') {?>
<script type="text/javascript">
    <!--
    window.print();
//-->
</script>
<?php
}

// echo footer
include 'templates/footer.php';
ob_end_flush();
