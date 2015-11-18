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
ob_start();

include_once 'directory.php';

$reportHelper = new ReportHelper();

if ($reportHelper->outputType === 'web'){
	$pageTitle = $reportHelper->report->getName();
	
	include 'templates/header.php';
}else if ($reportHelper->outputType === 'print'){
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CORAL Usage Statistics Reporting - <?php echo $reportHelper->report->getName(); ?></title>
<link rel="stylesheet" href="css/print.css" type="text/css"
	media="screen" />
</head>
<body>

<?php
}else if ($reportHelper->outputType === 'pop'){
	$pageTitle = $reportHelper->report->getName();
	include 'templates/header.php';
}else{
	
	// required to allow downloads in IE 6 and 7
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Transfer-Encoding: binary");
	
	header("Content-type: application/vnd.ms-excel;");
	header("Content-Disposition: attachment; filename='" . strtr($reportHelper->report->getName(), ' ', '_') . "'");
	
	echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body>";
}

?>
	
	
<center>
		<table class='noborder' style='width: 780px;'>
			<tr>
				<td class='noborder' align='center' colspan='2'>
					<table class='noborder' style='text-align: left;'>
						<tr>
<?php if ($reportHelper->outputType === 'web'){ ?>
<td class="head" align='left' valign='top'
								style="min-height: 98px; width: 480px; background-image: url('images/reportingtitlereport.gif'); background-repeat: no-repeat;"><a
								href='index.php'
								style="border: none; outline: none; -moz-outline-style: none;"><img
									src='images/transparent.gif'
									style='width: 480px; height: 98px; border: none'></a></td>
							<td class='noborder'
								style='height: 98px; max-height: 124px; min-width: 300px; width: 100%'
								align='left' valign='bottom'>
								<table class="noborder"
									style="min-width: 300px; min-height: 98px; max-height: 124px; width: 100%">
									<tr valign="bottom" style="vertical-align: bottom;">
										<td class="head" style="padding: 5px; vertical-align: bottom;">
											<form name='viewreport' method='post' target='_self'>
<?php echo $reportHelper->hidden_inputs->getStr(); ?>
<input type="hidden" name="sortColumn"
													value='<?php echo $reportHelper->sortColumn; ?>'> <input
													type="hidden" name="sortOrder"
													value='<?php echo $reportHelper->sortOrder; ?>'> <input
														type="hidden" name="outputType" value='web'> <input
															type="hidden" name="useHidden" value=1> <font size="+1"><?php echo $reportHelper->report->getName(); ?></font>&nbsp;
																<a
																href="javascript:showPopup('report','<?php echo $reportHelper->report->getID(); ?>');"
																title="<?php echo _("Click to show information about this report");?>"
																style="border: none"><img src='images/help.gif'
																	style="border: none"></a><br>
<?php echo $reportHelper->paramDisplay; ?>
<a
																	href="index.php?&reportID=<?php echo $reportHelper->report->getID() . $reportHelper->rprt_prm_add; ?>"><?php echo _("Modify Parameters");?></a>&nbsp; <a href="index.php"><?php echo _("Create New Report");?></a> <br /> <a
																	href="javascript:viewReportOutput('xls');"
																	style="border: none"><img border='0'
																		src="images/xls.gif"></a> <a
																	href="javascript:viewReportOutput('print');"
																	style="border: none"><img border='0'
																		src="images/printer.gif"></a><br>
											
											</form>

										</td>
										<td class="head" align="right" valign="top">&nbsp;</td>
									</tr>
								</table>
							</td>
<?php }else{ ?>
<td class='head'><font size="+1"><?php echo $reportHelper->report->getName(); ?></font><br>
<?php echo $reportHelper->paramDisplay; ?>
<br /></td>
<?php
}
?>
				</tr> 
					<?php
					unset($reportHelper->hidden_inputs, $reportHelper->rprt_prm_add, $reportHelper->paramDisplay);
					
					$notes = new ReportNotes($reportHelper->report->getDBName());
					
					$reportArray = $reportHelper->getReportResults(false);
					$textAdd = (($reportHelper->report->getID() === '1') || ($reportHelper->report->getID() === '2')) ? _('By Month and Resource') : '';
					if ($reportHelper->outputType === 'web'){
						?>
<tr>
							<td colspan="2" class="rtitle">
<?php echo _("Number of Successful Full-Text Article Requests") .' '. $textAdd; ?>
</td>
						</tr>
						<tr>
							<td colspan="2" class="shadednoborder">
								<table id='R1' class="table rep-res" style="width: 100%">
<?php $reportHelper->process($reportArray,$notes); ?>
</table>
							</td>
						</tr>
<?php } else { ?>
<tr>
							<td colspan='2' align='left' class='noborder'><font size="+1"><?php echo _("Number of Successful Full-Text Article Requests") .' '. $textAdd; ?></font>
							</td>
						</tr>
						<tr>
							<td colspan='2' align='center' class='noborder'>
								<table id='R1' class="table rep-res" border='1'>
<?php $reportHelper->process($reportArray,$notes); ?>
</table>
							</td>
						</tr><?php
					}
					$reportArray = $reportHelper->getReportResults(true); // archive query
					
					if ($reportArray){
						if ($reportHelper->outputType === 'web'){
							?>
<tr>
							<td colspan="2" class="rtitle">
<?php echo _("Number of Successful Full-Text Article Requests from an Archive") .' '. $textAdd; ?>
</td>
						</tr>
						<tr>
							<td colspan="2" class="shadednoborder">
								<table id='R2' class="table rep-res" style="width: 100%">
<?php $reportHelper->process($reportArray,$notes); ?>
</table>
							</td>
						</tr><?php
						}else{
							?>
<tr>
							<td colspan='2' align='left' class='noborder'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='2' align='left' class='noborder'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='2' align='left' class='noborder'><font size="+1"><?php echo _("Number of Successful Full-Text Article Requests from an Archive") .' '. $textAdd; ?></font>
							</td>
						</tr>
						<tr>
							<td colspan='2' align='center' class='noborder'>
								<table id='R2' class="table rep-res" border='1'>
<?php $reportHelper->process($reportArray,$notes); ?>
</table>
							</td>
						</tr><?php
						}
					}
					?>
</table>
				</td>
			</tr>
			<tr>
				<td class='noborder' style='text-align: left;'><br /> <br />
<?php

// echo $rprt_sql;
// for excel
$modcolcount = $reportArray->numFields - 2;

if ($reportHelper->outputType != 'xls'){
	?>
<table style='width: 350px; border-width: 1px'>
						<tr>
							<td colspan='2'><b><?php echo _("Key");?></b></td>
						</tr>
<?php
	
	if (!$reportHelper->showUnadjusted){
		?>
<tr>
							<td class='flagged'>&nbsp;</td>
							<td><?php echo _("Programmatically flagged as outlier based on previous 12 month average. The number has not been adjusted.");?></td>
						</tr>
						<tr>
							<td class='overriden'>&nbsp;</td>
							<td><?php echo _("Programmatically flagged as outlier based on previous 12 month average. The number has been adjusted manually by Electronic Resources.");?></td>
						</tr>
						<tr>
							<td class='merged'>&nbsp;</td>
							<td><?php echo _("Multiple titles with the same print ISSN (generally multiple parts) have been merged together.");?></td>
						</tr>
<?php
	}else{
		?>
<tr>
							<td class='l1'>&nbsp;</td>
							<td><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[1]['overageCount']._(" over "). $reportHelper->outlier[1]['overagePercent'] .'% '. _("of the previous 12 month average.");?> </td>
						</tr>
						<tr>
							<td class='l2'>&nbsp;</td>
							<td><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[2]['overageCount']._(" over "). $reportHelper->outlier[2]['overagePercent'].'% '. _("of the previous 12 month average.");?> </td>
						</tr>
						<tr>
							<td class='l3'>&nbsp;</td>
							<td><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[3]['overageCount']._(" over "). $reportHelper->outlier[3]['overagePercent'].'% '. _("of the previous 12 month average.");?> </td>
						</tr>
						<tr>
							<td class='merged'>&nbsp;</td>
							<td><?php echo _("Multiple titles with the same print ISSN (generally multiple parts) have been merged together.");?></td>
						</tr>
<?php
	}
	?>
</table>
<?php
	// excel
}else{
	?>
<table style='border-width: 1px'>

<?php
	
	if (!$reportHelper->showUnadjusted){
		?>
<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder' align='right'><b><?php echo _("Color Background Key");?></b></td>
										<td class='flagged'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Programmatically flagged as outlier based on previous 12 month average. The number has not been adjusted.");?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
										<td class='overriden'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Programmatically flagged as outlier based on previous 12 month average. The number has been adjusted manually by Electronic Resources.");?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
										<td class='merged'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Multiple titles with the same print ISSN (generally multiple parts) have been merged together.");?></td>
									</tr>
								</table>
							</td>
						</tr>
<?php
	}else{
		
		?>
<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder' align='right'><b><?php echo _("Color Background Key");?></b></td>
										<td style='width: 20px;'
											bgcolor='<?php echo Color::$levelColors[1][2]; ?>'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[1]['overageCount']._(" over "). $reportHelper->outlier[1]['overagePercent'].'% '. _("of the previous 12 month average.");?> </td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
										<td style='width: 20px;'
											bgcolor='<?php echo Color::$levelColors[2][2]; ?>'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[2]['overageCount']._(" over "). $reportHelper->outlier[2]['overagePercent'].'% '._(" of the previous 12 month average.");?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
										<td style='width: 20px;'
											bgcolor='<?php echo Color::$levelColors[3][2]; ?>'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Programmatically flagged as outlier using the following formula: Count is").' '. $reportHelper->outlier[3]['overageCount']._(" over "). $reportHelper->outlier[3]['overagePercent'].'% '. _("of the previous 12 month average.");?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='<?php echo $reportArray->numFields; ?>'>
								<table style='border: 0px;'>
									<tr>
										<td class='noborder'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
										<td class='merged'>&nbsp;</td>
										<td class='noborder' colspan='<?php echo $modcolcount; ?>'><?php echo _("Multiple titles with the same print ISSN (generally multiple parts) have been merged together.");?></td>
									</tr>
								</table>
							</td>
						</tr>
	<?php
	}
}
unset($reportHelper->showUnadjusted, $reportHelper->outlier);
?>
<tr>
							<td class='noborder' style='text-align: left;'>
<?php if( $notes->hasPlatforms() ){ ?>
<br> <br>
										<table style='border-width: 1px'>
											<tr>
												<td colspan='3'><b><?php echo _("Platform Interface Notes (if available)");?></b>
												</td>
											</tr>
<?php $reportHelper->report->printPlatformInfo($notes->platformNotes()); ?>
</table><?php
}

if ($notes->hasPublishers()){
	?>
<br><br>
												<table style="border-width: 1px">
													<tr>
														<td colspan="3"><b><?php echo _("Publisher Notes (if available)");?></b></td>
													</tr>
<?php $reportHelper->report->printPublisherInfo($notes->publisherNotes()); ?>
</table><?php
}
?>

							
							
							
							
							
							</td>
						</tr>
					</table> <br /></td>
				<td class='noborder'>&nbsp;</td>
			</tr>
		</table>
	</center>


	<script type="text/javascript" src="js/report.js"></script>


<?php if ($reportHelper->outputType === 'print') { ?>
<script type="text/javascript">
//<!--
window.print();
//-->
</script>
<?php
}

// echo footer
include 'templates/footer.php';
ob_end_flush();
?>
