<?php

/*
**************************************************************************************************************************
** CORAL Usage Statistics Reporting Module v. 1.0
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/


session_start();

include_once 'directory.php';


$reportID = $_REQUEST['reportID'];
$outputType = $_REQUEST['outputType'];
$startPage = $_REQUEST['startPage'];
$numberOfRecords = $_REQUEST['numberOfRecords'];
$sortColumn = $_REQUEST['sortColumn'];
$sortOrder = $_REQUEST['sortOrder'];
$useHidden = $_REQUEST['useHidden'];
$titleID = $_REQUEST['titleID'];

if (!$reportID){
	header("Location: index.php");
}

if (!$outputType){
	$outputType = 'web';
}

//defaults if not entered
if (!$startPage) $startPage = 1;
$endPage = 100000;

$report = new Report(new NamedArguments(array('primaryKey' => $reportID)));

$hidden_inputs ='<input type="hidden" name="reportID" value="' . $reportID . '">';


//need to define this here rather than in css for excel
$usageFlaggedColor = "#738291"; //light blue
$usageOverridenColor = "#a9c0d8"; //blue
$usageMergedColor = "#d8d5da";
$levelColors = array ( array('','',''), array('#e9e33c', 'levelOne','yellow'),
array('#e9913c"', 'levelTwo','orange'),
array('#e93c3c"', 'levelThree','red'));
$showUnadjusted = 'N';



//Get the report parameters
//first do the title parameter if entered
if ($titleID != ''){
	$title = $report->getUsageTitle($titleID);

    $paramDisplay = "<b>Title:</b> " . $title . "<br />";
	$rprt_prm_add = '&titleID=' . $titleID;
	$hidden_inputs .= '<input type="hidden" name="titleID" value="' . $titleID . '">';
}


$parm = new ReportParameter();

//loop through each parameter
foreach ($report->getParameters() as $parm) {

  if (($parm->parameterTypeCode == 'ms') && ($useHidden == '')){
      $prm_value = $_REQUEST["prm_" . $parm->reportParameterID];
      $prm_value = preg_replace('/\\\\/', ',',$prm_value);
      $values = array();
      $values = explode(',', $prm_value);
      $prm_value = implode("', '",$values);
  }else{
      $prm_value = $_REQUEST["prm_" . $parm->reportParameterID];
      $prm_value = preg_replace('/^\s+/', '', $prm_value);
      $prm_value = preg_replace('/\s+$/', '', $prm_value);

  }

  $rprt_prm_add .= '&prm_' . $parm->reportParameterID . '=' . $prm_value;

  if ($prm_value){
     if ($parm->parameterTypeCode == 'chk'){
		if (($prm_value == 'on') || ($prm_value == 'Y')){
		  $showUnadjusted = 'Y';
		  $hidden_inputs .= '<input type="hidden" name="prm_' . $parm->reportParameterID . '" value="Y">';
		  $paramDisplay .= "<b>Numbers are not adjusted for use violations</b><br />";
		}
     }else if ($parm->parameterAddWhereClause == 'limit'){
		//decide what to do
		$add_where='';
		$max_rows=$prm_value;
		$paramDisplay = $paramDisplay . "<b>Limit:</b> Top " . $prm_value . "<br />";
     }else{

		//if the parm comes through as an id (for publisher / platform or title), display actual value for user friendliness
		if (($parm->parameterDisplayPrompt == 'Provider / Publisher') || ($parm->parameterDisplayPrompt == 'Provider') || ($parm->parameterDisplayPrompt == 'Publisher')){

			$displayValue = implode(", ",$parm->getPubPlatDisplayName($prm_value));

			$paramDisplay = $paramDisplay . "<b>" . $parm->parameterDisplayPrompt . ":</b> " . $displayValue . "<br />";
		}else{
			// only display the param info at the top if it was entered
				$paramDisplay = $paramDisplay . "<b>" . $parm->parameterDisplayPrompt . ":</b> '" . $prm_value . "'<br />";
		}


		//now make the actual change to the SQL statement parameter
		if ($parm->parameterAddWhereNumber == 2){
			$addWhere2 .= ' AND ' . $parm->parameterAddWhereClause;
			$prm_value=strtoupper($prm_value);
			$addWhere2 = preg_replace('/PARM/', $prm_value, $addWhere2);
		}else{
			$addWhere .= ' AND ' . $parm->parameterAddWhereClause;
			$prm_value=strtoupper($prm_value);
			$addWhere = preg_replace('/PARM/', $prm_value, $addWhere);
		}
		$hidden_inputs .= '<input type="hidden" name="prm_' . $parm->reportParameterID . '" value="' . $prm_value . '">';
      }
   }
}

//if titleID was passed in, add that to addwhere
if (($reportID == '1') && ($titleID != '')){
	$addWhere2 .= ' AND t.titleID = ' . $titleID;
}



// Get the report summing columns into sumColsArray for faster lookup later
//returns array of objects
$sumColsArray = array();
foreach($report->getReportSums() as $reportSum) {
	$sumColsArray[$reportSum->reportColumnName] = $reportSum->reportAction;
}

// Get the report grouping columns into groupColsArray for faster lookup later
$groupColsArray = array();
//returns array of objects
foreach($report->getGroupingColumns() as $reportGroup) {
	$groupColsArray[$reportGroup->reportGroupingColumnName] = 'N';
	$perform_subtotal_flag='Y';
}



//formulate report SQL

// Figure archive sql
$rprt_sql = $report->reportSQL;

// only use tsm if tsm is used in the sql statement - may not be the case for yearly rollups
if (preg_match('/mus/i',$rprt_sql)){
	$archiveAddWhere = $addWhere . ' AND mus.archiveInd = 1';
	$archive_rprt_sql = $report->reportSQL;

	// replace ADD_WHERE with the new $addWhere
	$addWhere .= ' AND mus.archiveInd = 0';
}else{
	$archiveAddWhere = $addWhere . ' AND yus.archiveInd = 1';
	$archive_rprt_sql = $rprt_sql;

	// replace ADD_WHERE with the new $addWhere
	$addWhere .= ' AND yus.archiveInd = 0';
}

$archive_rprt_sql = preg_replace('/ADD_WHERE2/', $addWhere2, $archive_rprt_sql);
$archive_rprt_sql = preg_replace('/ADD_WHERE/', $archiveAddWhere, $archive_rprt_sql);

$rprt_sql = preg_replace('/ADD_WHERE2/', $addWhere2, $rprt_sql);
$rprt_sql = preg_replace('/ADD_WHERE/', $addWhere, $rprt_sql);

//add on the order
if ($sortColumn){
	$rprt_sql .= ' ORDER BY ' . $sortColumn . ' ' . $sortOrder;
}else{
	$rprt_sql .= ' ' . $report->orderBySQL;
}


//change default number of records per page if there is a report specific one
if (($rprt_dflt_rec_page_nbr) && ($numberOfRecords == '')) $numberOfRecords = $rprt_dflt_rec_page_nbr;
if (!$numberOfRecords) $numberOfRecords = 100000;

$endPage = $startPage + $numberOfRecords - 1;

$pageTitle = $report->reportName;


//Get outlier information
$outlierArray = array();
$outlier = array();

foreach($report->getOutliers() as $outlierArray) {
	$outlier[$outlierArray['outlierLevel']]['overageCount'] = $outlierArray['overageCount'];
	$outlier[$outlierArray['outlierLevel']]['overagePercent'] = $outlierArray['overagePercent'];
	$outlier[$outlierArray['outlierLevel']]['outlierLevel'] = $outlierArray['outlierLevel'];
}


if ($outputType == 'web'){
	include 'templates/header.php';

}else if ($outputType == 'print'){
	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>CORAL E-Journal Usage Statistics Reporting - <?php echo $pageTitle; ?></title>
		<link rel="stylesheet" href="css/print.css" type="text/css" media="screen" />
		</head>
		<body>

	<?php
}else if ($outputType == 'pop'){
	include 'templates/header.php';
}else{

	$excelfile = $report->reportName;
	$excelfile = str_replace (' ','_',$excelfile);

	//required to allow downloads in IE 6 and 7
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Transfer-Encoding: binary");

	header("Content-type: application/vnd.ms-excel;");
	header("Content-Disposition: attachment; filename='" . $excelfile . "'");

	echo "<html><head></head><body>";

}

?>


<center>
<table class='noborder' style='width:780px;'>
<tr>
<td class='noborder' align='center' colspan='2'>
        <table class='noborder' style='text-align:left;'>
        <tr>
<?php if ($outputType == 'web'){ ?>
        <td class="head" align='left' valign='top' style="min-height:98px;width:480px;background-image:url('images/reportingtitlereport.gif');background-repeat:no-repeat;"> <a href='index.php' style="border:none;outline: none;-moz-outline-style: none;"><img src='images/transparent.gif' style='width:480px;height:98px;border:none' /></a> </td>
        <td class='noborder' style='height:98px;max-height:124px;min-width:300px; width:100%' align='left' valign='bottom'>
                <table class="noborder" style="min-width:300px;min-height:98px;max-height:124px; width:100%">
                <tr valign="bottom" style="vertical-align:bottom;">
                <td class="head" style="padding:5px;vertical-align:bottom;">
					<form name='viewreport' method='post' target='_self'>
					<?php echo $hidden_inputs; ?>
					<input type="hidden" name="sortColumn" value='<?php echo $sortColumn; ?>'>
					<input type="hidden" name="sortOrder" value='<?php echo $sortOrder; ?>'>
					<input type="hidden" name="outputType" value='web'>
					<input type="hidden" name="useHidden" value=1>
					<font size="+1"><?php echo $report->reportName; ?></font>&nbsp;
					<a href="javascript:showPopup('report','<?php echo $reportID; ?>');" title='Click to show information about this report' style="border:none"><img src='images/help.gif' style="border:none"></a><br />
					<?php echo $paramDisplay; ?>
					<a href="index.php?&reportID=<?php echo $reportID . $rprt_prm_add; ?>">Modify Parameters</a>&nbsp;
					<a href="index.php">Create New Report</a>
					<br />
					<a href="javascript:viewReportOutput('xls');" style="border:none"><img border='0' src="images/xls.gif"></a>
					<a href="javascript:viewReportOutput('print');" style="border:none"><img border='0' src="images/printer.gif"></a><br />
					</form>

                </td>
                <td class="head" align="right" valign="top">&nbsp;</td>
                </tr>
                </table>
        </td>
        </tr>
<?php }else{ ?>
	<td class='head'>
	<font size="+1"><?php echo $report->reportName; ?></font><br />
	<?php echo $paramDisplay; ?>
	<br />
	</td>
	</tr>
<?php
}

$platArray = array();
$pubArray = array();


// determine how many times we should loop through the report (loop twice if there are archive records)
$archiveArray = $report->getReportResults($archive_rprt_sql);
if (count($archiveArray) > 0){
	$numberLoops = 2;
}else{
	$numberLoops = 1;
}


for($a = 1; $a <= $numberLoops; $a++){

  $rowcount = 1;
  $rowoutput = array();
  $sumArray = array();
  $totalSumArray = array();
  $holdArray = array();
  $fieldNameArray = array();

  if (($reportID == '1') || ($reportID == '2')){
	$textAdd = "By Month and Journal";
  }

  //archive loop
  $reportArray = array();
  if ($a == 2){
  	$reportArray = $archiveArray;

	if ($outputType == 'web'){
		?>
			</table>
        	<tr>
        	<td colspan="2" class="shadednoborder" style='text-align:left;border-left: 1px solid #c6d7e8;'>
			<br />
			<font size="+1">Number of Successful Full-Text Article Requests from an Archive <?php echo $textAdd; ?></font>
			</td>
			</tr>
        	<tr>
        	<td colspan="2" class="shadednoborder">
		<table style="width:100%">
<?php }else{ ?>
		</table>
		<tr><td colspan='2' align='left' class='noborder'>&nbsp;</td></tr>
		<tr><td colspan='2' align='left' class='noborder'>&nbsp;</td></tr>

		<tr>
		<td colspan='2' align='left' class='noborder'>
		<font size="+1">Number of Successful Full-Text Article Requests from an Archive <?php echo $textAdd; ?></font>
		</td>
		</tr>
		<tr>
		<td colspan='2' align='center' class='noborder'>
		<table border='1'>
	<?php
	}
  }else{ //if on the first loop
	// run the report sql we just created for the actual report output
	$reportArray = $report->getReportResults($rprt_sql);

	if ($outputType == 'web'){
	?>
        	<tr>
        	<td colspan="2" class="shadednoborder" style='text-align:left;border-left: 1px solid #c6d7e8;'>
			<font size="+1">Number of Successful Full-Text Article Requests <?php echo $textAdd; ?></font>
			</td>
			</tr>
        	<tr>
        	<td colspan="2" class="shadednoborder">
		<table style="width:100%">
	<?php }else{ ?>
		<tr>
		<td colspan='2' align='left' class='noborder'>
		<font size="+1">Number of Successful Full-Text Article Requests <?php echo $textAdd; ?></font>
		</td>
		</tr>
		<tr>
		<td colspan='2' align='center' class='noborder'>
		<table border='1'>
	<?php
	}

  }

  //loop through resultset
  foreach ($reportArray as $rowArray){
	$groupArrayCount=count($groupColsArray);
	$titleID = $rowArray['titleID'];
	$overrides = array('JAN' => $rowArray['JAN_OVERRIDE'],
						 'FEB' => $rowArray['FEB_OVERRIDE'],
						 'MAR' => $rowArray['MAR_OVERRIDE'],
						 'APR' => $rowArray['APR_OVERRIDE'],
						 'MAY' => $rowArray['MAY_OVERRIDE'],
						 'JUN' => $rowArray['JUN_OVERRIDE'],
						 'JUL' => $rowArray['JUL_OVERRIDE'],
						 'AUG' => $rowArray['AUG_OVERRIDE'],
						 'SEP' => $rowArray['SEP_OVERRIDE'],
						 'OCT' => $rowArray['OCT_OVERRIDE'],
						 'NOV' => $rowArray['NOV_OVERRIDE'],
						 'DEC' => $rowArray['DEC_OVERRIDE'],
						 'YTD_TOTAL' => $rowArray['YTD_OVERRIDE'],
						 'YTD_HTML' => $rowArray['HTML_OVERRIDE'],
						 'YTD_PDF' => $rowArray['PDF_OVERRIDE']);

	$outliers =  array('JAN' => $rowArray['JAN_OUTLIER'],
						 'FEB' => $rowArray['FEB_OUTLIER'],
						 'MAR' => $rowArray['MAR_OUTLIER'],
						 'APR' => $rowArray['APR_OUTLIER'],
						 'MAY' => $rowArray['MAY_OUTLIER'],
						 'JUN' => $rowArray['JUN_OUTLIER'],
						 'JUL' => $rowArray['JUL_OUTLIER'],
						 'AUG' => $rowArray['AUG_OUTLIER'],
						 'SEP' => $rowArray['SEP_OUTLIER'],
						 'OCT' => $rowArray['OCT_OUTLIER'],
						 'NOV' => $rowArray['NOV_OUTLIER'],
						 'DEC' => $rowArray['DEC_OUTLIER']);

	$pub_plat_id = $rowArray['publisherPlatformID'];
	$plat_id = $rowArray['platformID'];

	$merge_ind = $rowArray['MERGE_IND'];
	$printISSN = $rowArray['PRINT_ISSN'];
	$onlineISSN = $rowArray['ONLINE_ISSN'];

	if ($plat_id) $platArray[] = $plat_id;
	if ($pub_plat_id) $pubArray[] = $pub_plat_id;


    if ($rowcount == $startPage){
        $reset='Y';
    }else{
       	$reset='N';
    }

	$performCheck='N';
	$print_subtotal_flag='N';

	$rowoutput[$rowcount] = "<tr border=1 class='data'>\n";

	$displayCols='Y';

	$index=0;
	foreach ($rowArray as $field => $data) {
		//stop displaying columns once we hit title ID or platform ID
		if (($field == 'titleID') || ($field == 'platformID')) $displayCols = 'N';

		if ($displayCols == 'Y') {
			if ($data == '') $data = '&nbsp;';

			//on the first row, load all of the display field names into array for later use
			if ($rowcount == "1"){
				$fieldNameArray[] = $field;
			}

			//if sort is explicitly requested we will group on this column if it is allowed according to DB
			$sortIndex=$index+1;
			if (($sortColumn == $sortIndex) && ($data != $holdArray[$index]) && (array_key_exists($field,$groupColsArray))){
				$hold_rprt_grpng_data = $holdArray[$index];
				$print_subtotal_flag='Y';
			//if no sort order is specified, use default grouping
			}else if (($sortOrder == '') && ($data != $holdArray[$index]) && ($holdArray[$index] != '') && (array_key_exists($field,$groupColsArray))){
				$groupColsArray[$field] = 'Y';
				//default echo flag to Y, we will reset later
				$print_subtotal_flag = 'Y';
				$performCheck='Y';
				if ($groupArrayCount == 1){
					$hold_rprt_grpng_data = $holdArray[$index];
				}else{
					$hold_rprt_grpng_data='Group';
				}
			}

			if (($data != $holdArray[$index]) || ($reset == 'Y') || ($outputType == 'xls') || (($perform_subtotal_flag == 'Y') && ($sortOrder == '') && ($groupArrayCount > 1))) {
				$reset = 'Y';
				$print_data = $data;
			} else {
				//echo it out if it's a number, needs to be printed regardless
				if (preg_match('/^[+-]?\d+$/', $print_data)){
					$print_data = '&nbsp;';
				}else{
					$print_data = $data;
				}
			}




			if (($outputType == 'web') && ($print_data != '&nbsp;')){
				if ($field == 'TITLE'){
					if ($reportID != '1'){
						$print_data .= "<br /><font size='-4'><a target='_BLANK' href='report.php?reportID=1&prm_4=" . $showUnadjusted . "&titleID=" . $titleID . "&outputType=web'>view related titles</a></font>";
					}

					//echo link resolver link
					$config = new Configuration();


					if ((($printISSN) || ($onlineISSN)) && ($config->settings->baseURL)){
						if (($printISSN) && !($onlineISSN)){
							$urlAdd = "rft.issn=" . $printISSN;
						}else if (($printISSN) && ($onlineISSN)){
							$urlAdd = "rft.issn=" . $printISSN . "&rft.eissn=" . $onlineISSN;
						}else{
							$urlAdd = "rft.eissn=" . $onlineISSN;
						}


						$resolverURL = $config->settings->baseURL;

						//check if there is already a ? in the URL so that we don't add another when appending the parms
						if (strpos($resolverURL, "?") > 0){
							$resolverURL .= "&";
						}else{
							$resolverURL .= "?";
						}

						$resolverURL .= $urlAdd;


						$print_data .= "<br /><font size='-4'><a target='_BLANK' href='" . $resolverURL . "'>view in link resolver</a></font>";
					}


				}

				//no longer using since notes are printed at the bottom of the screen
				//if (($field == 'PLATFORM') && (preg_match('/^[+-]?\d+$/',$plat_id))){
				//	$print_data .= "<br /><font size='-4'><a href=\"javascript:showPubPlat('',$plat_id);\">view platform notes</a></font>";
				//}
				//if (($field == 'Publisher') && (preg_match('/^[+-]?\d+$/', $pub_plat_id))){
				//	if (count($report->getPublisherInformation($pub_plat_id)) > 0){
				//		$print_data .= "<br /><font size='-4'><a href=\"javascript:showPubPlat($pub_plat_id,'');\">view publisher notes</a></font>";
				//	}
				//}

			}

			//Display adjusted numbers
			if (($showUnadjusted == 'N') && (isset($overrides[$field]) || isset($outliers[$field]))){

				if (isset($overrides[$field])){
					$rowoutput[$rowcount] .= "<td bgcolor='$usageOverridenColor' class='usageOverriden' border=1>" . $overrides[$field] . '</td>';
				}else if ($outliers[$field] > 0){
					$rowoutput[$rowcount] .= "<td bgcolor='$usageFlaggedColor' class='usageFlagged' border=1>" . $print_data . '</td>';
				}else{
					if ($rowcount % 2) {
							$rowoutput[$rowcount] .= "<td class='alt' border=1>" . $print_data . '</td>';
					}else{
							$rowoutput[$rowcount] .= "<td border=1>" . $print_data . '</td>';
					}
				}
			//display unadjusted numbers
			}else if (($showUnadjusted == 'Y') && ($outliers[$field] > 0)){
				if ($outliers[$field] >= 4) { $outliers[$field] = $outliers[$field] - 3;}
				$rowoutput[$rowcount] .= "<td bgcolor='" . $levelColors[$outliers{$field}][2] . "' class='" . $levelColors[$outliers[$field]][1] . "' border=1>" . $print_data . "</td>";

			}else if ($merge_ind == 1){
				$rowoutput[$rowcount] .= "<td bgcolor='$usageMergedColor' class='usageMerged' border=1>" . $print_data . '</td>';

			}else{
				if ($rowcount % 2) {
					$rowoutput[$rowcount] .= "<td class='alt' border=1>" . $print_data . '</td>';
				}else{
					$rowoutput[$rowcount] .= "<td border=1>" . $print_data . '</td>';
				}
			}

			$holdArray[$index] = $data;

		}//end if display columns is Y
		$index++;
	}//end loop through columns

	// loop through the group arrays, if any are N then echo flag is N otherwise it will be left to Y
	if ($performCheck == 'Y'){
		foreach ($groupColsArray as $key=>$value) {
			if ($value == 'N'){
				$print_subtotal_flag='N';
			}
		}
	}

	//determine if the previous row needs to be grouped
	if (($outputType != 'xls') && ($print_subtotal_flag == 'Y') && ($report->groupTotalInd == '1')){
		$groupRow=$rowcount-.5;
		$rowoutput[$groupRow] .= "</tr>\n";

		if ($countForGrouping > 1){
			$rowoutput[$groupRow] .= "<tr class='data'>\n";
			$index=0;
			foreach ($fieldNameArray as $sumfield) {

				$total='';

				if ($index==0){
					$rowoutput[$groupRow] .= "<td class='sum'>Total for " . $hold_rprt_grpng_data . "</td>";
					$index++;
				}else{
					if ($sumColsArray[$sumfield] == 'dollarsum'){
						foreach ($sumArray[$sumfield] as $amt){
							$total += $amt;
						}
						if ($total > 0){
							$total = money_format($total);
						}else{
							$total = "&nbsp;";
						}

					}else if ($sumColsArray[$sumfield] == 'sum'){
						foreach ($sumArray[$sumfield] as $amt){
							if ($amt >= "0"){
								$total += $amt;
							}
						}
						if ($total >= "0"){
							$total = number_format($total);
						}else{
							$total = "&nbsp;";
						}

					}else{
						$total="&nbsp;";
					}

				$rowoutput[$groupRow] .= "<td class='sum'>" . $total . "</td>";
				}

			}
			$rowoutput[$groupRow] .= "</tr>\n";
		}

		$sumArray = array();
		$countForGrouping=0;
		$rowoutput[$groupRow] .= "<tr class='data'><td colspan=" . count($fieldNameArray) . ">&nbsp;</td></tr>\n";
		$rowoutput[$groupRow] .= "<tr class='data'>\n";
	}

	// Now that we've figured out the summing as of the previous row, we can add in this row's summing
	$index=0;
	$displaySumCols='Y';
	foreach ($rowArray as $field => $data) {
		//stop checking columns once we hit title ID or platform ID
		if (($field == 'titleID') || ($field == 'platformID')) $displaySumCols = 'N';

		if ($displaySumCols == 'Y'){
			$sortIndex=$index+1;
			if (($sortColumn == $sortIndex) && (array_key_exists($field, $groupColsArray))){
				$hold_rprt_grpng_data = $holdArray[$index];
			}else if (($sortOrder == '') && (array_key_exists($field, $groupColsArray))){
				$groupArrayCount=count($groupColsArray);
				if ($groupArrayCount == 1){
					$hold_rprt_grpng_data = $holdArray[$index];
				}else{
					$hold_rprt_grpng_data='Group';
				}
			}


			// get the numbers out for summing
			$data = preg_replace('/[^0-9.-]/','', $data);
			$sumArray[$field][] = $data;
			$totalSumArray[$field] = $totalSumArray[$field] + $data;
		}
		$index++;
	}



	$rowcount++;
	$countForGrouping++;

}
$rowcount--;

$displayCols = 'Y';
$colcount=1;

if ($rowcount > 0){

	echo "<tbody id='mybody'><tr valign='bottom' id='eee'>\n";

	foreach ($reportArray[0] as $field => $data) {
		//stop displaying columns once we hit title ID or platform ID
		if (($field == 'titleID') || ($field == 'platformID')) $displayCols = 'N';

		if ($displayCols == 'Y') {
			$fieldDisplay = $field;


			$fieldDisplay = str_replace("_", " ", $field);
			$fieldDisplay = ucwords(strtolower($fieldDisplay));

			echo "<th style='align:center;'>" . $fieldDisplay;
			$displaySortColumn=$colcount;

			$ascColumnSel='';
			$descColumnSel='';
			if (($sortColumn==$displaySortColumn) && ($sortOrder == 'asc')) $ascColumnSel='_sel';
			if (($sortColumn==$displaySortColumn) && ($sortOrder == 'desc')) $descColumnSel='_sel';

			if ($outputType == 'web'){
				?>
				<div style='width:100%;min-width:22px;align:center;margin:0px;padding:0px;'>
				<a href="javascript:sortRecords('<?php echo $displaySortColumn; ?>', 'asc');"  style="border:none">
				<img align='center' src='images/arrowdown<?php echo $ascColumnSel; ?>.gif' border=0></a>&nbsp;
				<a href="javascript:sortRecords('<?php echo $displaySortColumn; ?>', 'desc');"  style="border:none">
				<img align='center' src='images/arrowup<?php echo $descColumnSel; ?>.gif' border=0></a>
				</div>
				<?php
			}
			echo "</th>\n";
			$colcount++;
		}
	}
	echo "</tr>\n";
}

//echo the data table!!
if (($max_rows > 0) && ($max_rows <= count($rowoutput))){
	$resizedArray = array();
	$resizedArray = array_slice($rowoutput, 0, $max_rows);

	foreach ($resizedArray as $printout){
		echo $printout;
	}
}else{
	foreach ($rowoutput as $printout){
		echo $printout;
	}
}


//  one last grouping summary
if (($outputType != 'xls') && ($perform_subtotal_flag == 'Y') && ($report->groupTotalInd == '1') && ($hold_rprt_grpng_data)) {
	if ($countForGrouping > 1){
		echo "<tr class='data'>\n";
		$index=0;
		foreach ($fieldNameArray as $sumfield) {

			$total='';

			if ($index==0){
				echo "<td class='sum'>Total for " . $hold_rprt_grpng_data . "</td>";
				$index++;
			}else{
				if ($sumColsArray[$sumfield] == 'dollarsum'){
					foreach ($sumArray[$sumfield] as $amt){
						$total += $amt;
					}
					if ($total > 0){
						$total = money_format($total);
					}else{
						$total = "&nbsp;";
					}

				}else if ($sumColsArray[$sumfield] == 'sum'){
					foreach ($sumArray[$sumfield] as $amt){
						if ($amt >= "0"){
							$total += $amt;
						}
					}
					if ($total >= "0"){
						$total = number_format($total);
					}else{
						$total = "&nbsp;";
					}

				}else{
					$total="&nbsp;";
				}

			echo "<td class='sum'>" . $total . "</td>";
			}

		}
		echo "</tr>\n";
	}

	$sumArray = array();
	echo "<tr class='data'><td colspan=" . count($fieldNameArray) . ">&nbsp;</td></tr>\n";
	}
}

if (($outputType != 'xls') && ($perform_subtotal_flag == 'Y')) {
	echo "<tr class='data'>";
	echo "<td class='sum'>Total for Report</td>";
	$colNum=0;


	foreach ($fieldNameArray as $field) {
		$colNum++;
		//don't print first col since we already put "Total" text in
		if ($colNum > 1){
			$sumfield=$field;
			$total='';

			if ($sumColsArray[$sumfield] == 'dollarsum'){
				$total = money_format($totalSumArray[$sumfield]);
			}else if ($sumColsArray[$sumfield] == 'sum'){
				$total=number_format($totalSumArray[$sumfield]);
				if (!$total) $total = '-';
			}else if ($sumColsArray[$sumfield] == 'avg'){
				if ($rowcount > 0 ) {
					$total = number_format($totalSumArray[$sumfield] / $rowcount) . '%';
				}else{
					$total='';
				}
			}

			if (!$total) $total='&nbsp;';
			echo "<td class='sum'>$total</td>";
		}

	}
	echo "</tr>\n";

}

if ($rowcount == 0){
	echo "<tr class='data'><td colspan='$colcount'><i>Sorry, no rows were returned.</i></td></tr>";
}else{
	if ($endPage > $rowcount) $endPage = $rowcount;
	if ($outputType != 'web') $startPage=1; $endPage=$rowcount;
	if (($max_rows > 0) && ($rowcount > $max_rows)){
		echo "<tr><td colspan='$colcount' align='right'><i>Showing rows $startPage to $max_rows of $max_rows</i></td></tr>";
	}else{
		echo "<tr><td colspan='$colcount' align='right'><i>Showing rows $startPage to $rowcount of $rowcount</i></td></tr>";

	}
}

?>
</tbody>
</table>
</center>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class='noborder' style='text-align:left;'>
<br />
<br />
<?php

//echo $rprt_sql;
//for excel
$modcolcount = $colcount-2;

if ($outputType != 'xls'){
	?>
	<table style='width:350px;border-width:1px'>
	<tr>
	<td colspan='2'><font style='font-weight:bold'>Key</font></td>
	</tr>
	<tr>
	<?php

	if ($showUnadjusted == 'N') {
	?>
	<td style='width:20px;' bgcolor='<?php echo $usageFlaggedColor; ?>' class='usageFlagged'>&nbsp;</td>
	<td>Programmatically flagged as outlier based on previous 12 month average.  The number has not been adjusted.</td>
	</tr>
	<tr>
	<td style='width:20px;' bgcolor='<?php echo $usageOverridenColor; ?>' class='usageOverriden'>&nbsp;</td>
	<td>Programmatically flagged as outlier based on previous 12 month average.  The number has been adjusted manually by Electronic Resources.</td>
	</tr>
	<tr>
	<td style='width:20px;' bgcolor='<?php echo $usageMergedColor; ?>' class='usageMerged'>&nbsp;</td>
	<td>Multiple titles with the same print ISSN (generally multiple parts) have been merged together.</td>
	<?php
	}else{
	?>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[1][0]; ?>' class='<?php echo $levelColors[1][1]; ?>'>&nbsp;</td>
	<td>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[1]['overageCount']; ?> over <?php echo $outlier[1]['overagePercent']; ?>% of the previous 12 month average. </td>
	</tr>
	<tr>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[2][0]; ?>' class='<?php echo $levelColors[2][1]; ?>'>&nbsp;</td>
	<td>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[2]['overageCount']; ?> over <?php echo $outlier[2]['overagePercent']; ?>% of the previous 12 month average. </td>
	</tr>
	<tr>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[3][0]; ?>' class='<?php echo $levelColors[3][1]; ?>'>&nbsp;</td>
	<td>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[3]['overageCount']; ?> over <?php echo $outlier[3]['overagePercent']; ?>% of the previous 12 month average. </td>
	</tr>
	<tr>
	<td style='width:20px;' bgcolor='<?php echo $usageMergedColor; ?>' class='usageMerged'>&nbsp;</td>
	<td>Multiple titles with the same print ISSN (generally multiple parts) have been merged together.</td>
	<?php
	}
	?>
	</tr>
	</table>
	<?php
//excel
}else{
	?>
	<table style='border-width:1px'>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder' align='right'><font style='font-weight:bold'>Color Background Key</font></td>
	<?php


	if ($showUnadjusted == 'N') {
	?>
	<td style='width:20px;' bgcolor='<?php echo $usageFlaggedColor; ?>' class='usageFlagged'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Programmatically flagged as outlier based on previous 12 month average.  The number has not been adjusted.</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style='width:20px;' bgcolor='<?php echo $usageOverridenColor; ?>' class='usageOverriden'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Programmatically flagged as outlier based on previous 12 month average.  The number has been adjusted manually by Electronic Resources.</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style='width:20px;' bgcolor='<?php echo $usageMergedColor; ?>' class='usageMerged'>&nbsp;</td>
	<td class='noborder'colspan='<?php echo $modcolcount; ?>'>Multiple titles with the same print ISSN (generally multiple parts) have been merged together.</td>
	</tr>
	</table>
	<?php

	}else{

	?>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[1][2]; ?>'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[1]['overageCount']; ?> over <?php echo $outlier[1]['overagePercent']; ?>% of the previous 12 month average. </td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[2][2]; ?>'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[2]['overageCount']; ?> over <?php echo $outlier[2]['overagePercent']; ?>% of the previous 12 month average.</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style='width:20px;' bgcolor='<?php echo $levelColors[3][2]; ?>'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Programmatically flagged as outlier using the following formula: Count is <?php echo $outlier[3]['overageCount']; ?> over <?php echo $outlier[3]['overagePercent']; ?>% of the previous 12 month average.</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='<?php echo $colcount; ?>'>
	<table style='border:0px;'>
	<tr>
	<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td style='width:20px;' bgcolor='<?php echo $usageMergedColor; ?>' class='usageMerged'>&nbsp;</td>
	<td class='noborder' colspan='<?php echo $modcolcount; ?>'>Multiple titles with the same print ISSN (generally multiple parts) have been merged together.</td>
	</tr>
	</table>
	<?php

	}

}

?>
</td>
</tr>
<tr>
<td class='noborder' style='text-align:left;'>
<br />
<br />

<?php

if (count($platArray) > 0){

	//pull out unique platforms
	$uniq_plat = array_unique($platArray);


	$platIDs = join(',', $uniq_plat);


	//echo out platform information

	echo "<table style='border-width:1px'>";
	echo "<tr><td colspan='3'><font style='font-weight:bold'>Platform Interface Notes (if available)</font></td></tr>";

	foreach ($report->getPlatformInformation($platIDs) as $platform) {
			echo "<tr valign='top'>";
			echo "<td align='right'><font style='font-weight:bold'>" . $platform['reportDisplayName'] . "</font></td>";

			if (($platform['startYear'] != '') && (($platform['endYear'] == '') || ($platform['endYear'] == '0'))){
					echo "<td>Year: " . $platform['startYear'] . " to present</td>";
			}else{
					echo "<td>Years: " . $platform['startYear'] . " to " . $platform['endYear'] . "</td>";
			}
			echo "<td>";

			if ($platform['counterCompliantInd'] == '1') {
					echo "This Interface provides COUNTER compliant stats.<br />";
			}else{
					echo "This Interface does not provide COUNTER compliant stats.<br />";
			}


			if ($platform['noteText']){
					echo "<br /><i>Interface Notes</i>: " . $platform['noteText'] . "<br />";
			}
			echo "</td>";
			echo "</tr>";

	}
	echo "</table>";

}

?>

<br />
<br />
<?php





//pull out unique publishers
$uniq_pub = array_unique($pubArray);


$pubIDs = join(',', $uniq_pub);


if ($pubIDs){
	//echo out platform information
	$publisherArray = $report->getPublisherInformation($pubIDs);

	if (count($publisherArray) > 0){
		echo "<table style='border-width:1px'>";
		echo "<tr><td colspan='3'><font style='font-weight:bold'>Publisher Notes (if available)</font></td></tr>";

		foreach ($publisherArray as $publisher) {

			echo "<tr valign='top'>";
			echo "<td align='right'><font style='font-weight:bold'>" . $publisher['reportDisplayName'] . "</font></td>";

				if (($publisher['startYear'] != '') && ($publisher['endYear'] == '')){
						echo "<td>Year: " . $publisher['startYear'] . "</td>";
				}else{
						echo "<td>Years: " . $publisher['startYear'] . " to " . $publisher['endYear'] . "</td>";
				}

				echo "<td>" . $publisher['notes'] . "</td>";

			echo "</tr>";

		}
		echo "</table>";
	}
}

?>
</table>
</td>
</tr>
</table>
<br />
</td>
<td class='noborder'>
&nbsp;
</td>
</tr>
</center>
</table>


<script type="text/javascript" src="js/header.js"></script>
<script type="text/javascript" src="js/report.js"></script>


<?php if ($outputType == 'print') { ?>
	<script language="javascript">
  	<!--
  	window.print();
  	//-->
	</script>
<?php
}

//echo footer
include 'templates/footer.php';
?>