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

//print header
$pageTitle=_('Home');
include 'templates/header.php';


//get all reports for output in drop down
$reportArray = array();
$reportObj = new Report();
$reportArray = $reportObj->allAsArray();


?>




<center>
<form name="reportlist" method="post" action="report.php">


	<table class='noborder' cellpadding="0" cellspacing="0" style="width:699px;text-align:left;">
	<tr>
	<td class="noborder" style="background-image:url('images/reportstitle.gif');background-repeat:no-repeat;text-align:right;">

	<span style="border:none;outline: none;-moz-outline-style: none; float:left;"><img src='images/transparent.gif' style='width:450px;height:100px;border:none' /></span>
	<div style='margin-right:5px; margin-top:35px; text-align:right;'>
	<span style='float:right; font-size:110%; color:#526972'>&nbsp;</span>
	</div>
	</td>
	</tr>
	<tr>
	<td class="fullborder"> <br />
	<br />
	<div id='div_report'>


	<label for="reportID"><?= _("Select Report");?></label>

	<select name='reportID' id='reportID' class='optionStyle'>
	<option value=''></option>
	<?php
	foreach ($reportArray as $report){
		if ($report['reportID'] == $_GET['reportID']){
			echo "<option value='" . $report['reportID'] . "' selected>" . $report['reportName'] . "</option>\n";
		}else{
			echo "<option value='" . $report['reportID'] . "'>" . $report['reportName'] . "</option>\n";
		}
	}
	?>
	</select>


	</div>

	<div id='div_parm'>
	<?php

		if ($_GET['reportID']){

			$reportID = $_GET['reportID'];
			$report = new Report(new NamedArguments(array('primaryKey' => $reportID)));


			//get parameters
			$parm = new ReportParameter();
			$parmValue = array();

			foreach ($report->getParameters() as $parm) {
				echo "<div id='div_parm_" . $parm->reportParameterID . "'>";

				if ($parm->parameterTypeCode == "dd"){
					echo "<br />";
					echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";

					//check if it's a parent
					if ($parm->isParent()){
						echo "<select name='prm_" . $parm->reportParameterID . "' id='prm_" . $parm->reportParameterID . "' class='optionStyle' onchange='javascript:updateChildren(" . $parm->reportParameterID . ");'>\n";
					}else{
						echo "<select name='prm_" . $parm->reportParameterID . "' id='prm_" . $parm->reportParameterID . "' class='optionStyle'>\n";
					}

					if ($parm->requiredInd != '1'){
						echo "<option value=''>"._("All")."</option>\n";
					}

					$rownumber=1;

					foreach ($parm->getSelectValues($_GET['prm_' . $parm->parentReportParameterID]) as $value){
						if (($rownumber == '1') && ($parm->requiredInd == '1')) $parmValue[$parm->reportParameterID] = $value[0];

						if ($_GET['prm_' . $parm->reportParameterID] == $value['cde']){
							echo "<option value='" . $value['cde'] . "' selected>" . $value['val'] . "</option>\n";
						}else{
							echo "<option value='" . $value['cde'] . "'>" . $value['val'] . "</option>\n";
						}

						$rownumber++;
					}

					echo "</select>";

				}else if ($parm->parameterTypeCode == "ms"){


					  $parms = array();
					  $passedParm = $_GET['prm_' . $parm->reportParameterID];

                	  if ($passedParm){
  	 	  				$passedParm=str_replace("'","",$passedParm);
  		  				$passedParm=str_replace(" ","",$passedParm);
                  		$parms = explode(",", $passedParm);
                	  }

					  echo "<br />";
					  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
					  echo "<span style='margin-left:-90px'>";

					  if (!$passedParm){
								echo "<div id='div_show_" . $parm->reportParameterID . "' style='float:left;margin-bottom: 5px'>";
								echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','block');toggleLayer('div_show_" . $parm->reportParameterID . "','none');\">-"._("Click to choose ") . $parm->parameterDisplayPrompt . "-</a>";
								echo "</div>";
								echo "<div id='div_" . $parm->reportParameterID . "' style='display:none;float:left;margin-bottom: 5px'>";
					  }else{
								echo "<div id='div_show_" . $parm->reportParameterID . "' style='display:none;float:left;margin-bottom: 5px'>";
								echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','block');toggleLayer('div_show_" . $parm->reportParameterID . "','none');\">-"._("Click to choose ") . $parm->parameterDisplayPrompt . "-</a>";
								echo "</div>";
								echo "<div id='div_" . $parm->reportParameterID . "' style='float:left;margin-bottom: 5px;'>";
					  }


					  echo "<table class='noborder'> <tr> <td class='noborder'>";
					  echo "<select name='prm_left_" . $parm->reportParameterID . "' id='prm_left_" . $parm->reportParameterID . "' class='optionStyle' size='10' multiple='multiple' style='width:175px'>\n";

					  if ($parm->requiredInd != '1'){
						echo "<option selected value=''>"._("All")."</option>\n";
					  }


					  $rightParms='';
					  foreach ($parm->getSelectValues($_GET['prm_' . $parm->parentReportParameterID]) as $value){
							$cde = str_replace("'","\\'", $value['cde']);
							$cde = str_replace(",","\\", $value['cde']);


						    #also perform check to add to the right hand side
						    $displayThis = 1;
						    if ($passedParm){
								foreach ($parms as $parmVal){

							  		if ($parmVal == $cde){
										$rightParms .=  "<option value='" . $cde . "'>" . $value['val'] . "</option>\n";
										$displayThis = 0;
							  		}
								}
						    }

						    if ($displayThis == '1'){
								echo "<option value='" . $cde . "'>" . $value['val'] . "</option>\n";
							}
					  }

					  echo "</select>\n";
					  echo "</td>\n";

					  //echo javascript left/right buttons
					  echo "<td align='center' valign='middle' style='border:0px;'>\n";
					  echo " <input type='button' value='--&gt;' style='width:35px' ";
					  echo "onclick='moveOptions(this.form.prm_left_" . $parm->reportParameterID . ", this.form.prm_right_" . $parm->reportParameterID . ");";
					  echo "placeInHidden(\",\",\"prm_right_" . $parm->reportParameterID . "\", \"prm_" . $parm->reportParameterID . "\");' /><br />\n";
					  echo " <input type='button' value='&lt;--' style='width:35px' ";
					  echo "  onclick='moveOptions(this.form.prm_right_" . $parm->reportParameterID . ", this.form.prm_left_" . $parm->reportParameterID . ");";
					  echo "placeInHidden(\",\",\"prm_right_" . $parm->reportParameterID . "\", \"prm_" . $parm->reportParameterID . "\");' />\n";
					  echo "</td>\n";
					  echo "<td style='border:0px;'>\n";
					  echo "<select name='prm_right_" . $parm->reportParameterID . "' id='prm_right_" . $parm->reportParameterID . "' class='optionStyle' size='10' multiple='multiple' style='width:175px'>\n";
					  if ($rightParms) echo $rightParms;
					  echo "</select>\n";
					  echo "</td></tr><tr><td style='border:0px;' colspan='3' align='left'>\n";
					  echo "<input type='hidden' name='prm_" . $parm->reportParameterID . "' id='prm_" . $parm->reportParameterID . "' value=\"" .  $passedParm . "\">";
					  echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','none');toggleLayer('div_show_" . $parm->reportParameterID . "','block');\">-"._("Hide ") . $parm->parameterDisplayPrompt . "-</a>";
					  echo "</td></tr></table>\n";
					  echo "</div>";
					  echo "</span>";

				}else if ($parm->parameterTypeCode == "chk"){

					  if (($_GET['prm_' . $parm->reportParameterID] == 'on') || ($_GET['prm_' . $parm->reportParameterID] == 'Y')){
					  	$checked = 'checked';
					  }else{
					  	$checked = '';
					  }

					  echo "<br />";
					  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
					  echo "<input type='checkbox' name='prm_" . $parm->reportParameterID . "' class='optionStyle' style='text-align:left;width:13px;' " . $checked . ">\n";

				}else{
					  if ($parm->parameterFormatCode == 'date') {
						$frmt_add='<font size="-2">ex: MM/DD/YYYY</font>';
					  }else{
						$frmt_add='';
					  }

					  echo "<br />";
					  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
					  echo "<input type='text' name='prm_" . $parm->reportParameterID . "' value='' class='optionStyle'>$frmt_add\n";

				}

				echo "</div>";


			}

		}else{
			echo "<br />";
		}

	?>

	</div>
	<input type='hidden' name='rprt_output' value='web'>
	<br />
	<br />
	<input type="submit" value="<?= _('Submit');?>" name="submitbutton" id="submitbutton">
	<input type="button" value="<?= _('Reset');?>" name="resetbutton" id="resetbutton" onclick="javascript:clearParms();">
	</td>
	</tr>
	</table>

</form>


<br />
<a href="mailto:Benjamin.J.Heet.2@ND.EDU"><?= _("Contact / Help");?></a>
</td>
</tr>
</table>
</form>
<br />
<br />


</center>

<script type="text/javascript" src="js/index.js"></script>

<?php
  //print footer
  include 'templates/footer.php';
?>