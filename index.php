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
ob_start();
include_once 'directory.php';

//print header
$pageTitle='Home';
include 'templates/header.php';


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
	
	
<label for="reportID">Select Report</label>

<select name='reportID' id='reportID' class='opt'>
<option value=''></option>
<?php
	//get all reports for output in drop down

	$db = new DBService;
	foreach($db->processQuery("SELECT reportID, reportName FROM Report ORDER BY 2, 1", MYSQLI_ASSOC) as $report){
		echo "<option value='" . $report['reportID'] . "' ";

		if (isset($report['reportID']) && isset($_GET['reportID'])
			&& $report['reportID']===$_GET['reportID']){

			echo 'selected';
		}
		echo ">" . $report['reportName'] . "</option>";
	}
	unset($db);
?>
</select>


</div>

<div id='div_parm'>
	<?php

	if (isset($_GET['reportID'])){

		$report = new Report($_GET['reportID']);

		
		//get parameters
		$parmValue = array();

		foreach ($report->getParameters() as $parm) {
			echo "<div id='div_parm_",
				$parm->reportParameterID,
				"'><br><label for='prm_",
				$parm->reportParameterID
				. "'>" . $parm->parameterDisplayPrompt,
				"</label>";

			if ($parm->parameterTypeCode === "dd") {
				echo "<select name='prm_"
					. $parm->reportParameterID
					. "' id='prm_"
					. $parm->reportParameterID
					. "' class='opt' ";
				if ($parm->isParent()){
					echo "onchange='javascript:updateChildren("
						. $parm->reportParameterID
						. ");')";
				}
				echo ">";

				if ($parm->requiredInd != '1'){
					echo "<option value=''>All</option>";
				}

				$rownumber=1;

				foreach ($parm->getSelectValues($_GET['prm_' . $parm->parentReportParameterID]) as $value){
					if (($rownumber === 1) && ($parm->requiredInd == '1')){
						$parmValue[$parm->reportParameterID] = $value[0];
					}
					echo "<option value='" . $value['cde'] . "'";

					if ($_GET['prm_' . $parm->reportParameterID] == $value['cde']){
						echo "selected";
					}

					echo '>' . $value['val'] . "</option>";
					++$rownumber;
				}
				
				echo "</select>";
				
			}else if ($parm->parameterTypeCode === "ms"){
				
				
				$parms = array();
				$passedParm = $_GET['prm_' . $parm->reportParameterID];

				echo "<span style='margin-left:-90px'><div id='div_show_"
					. $parm->reportParameterID
					. "' style='";
				if ($passedParm){
					$passedParm=str_replace(" ","",str_replace("'","",$passedParm));
					$parms = explode(",", $passedParm);
					echo "display:none;";
				}
				
				echo "float:left;margin-bottom: 5px'><a href=\"javascript:toggleLayer('div_",
					$parm->reportParameterID,
					"','block');toggleLayer('div_show_",
					$parm->reportParameterID,
					"','none');\">-Click to choose ",
					$parm->parameterDisplayPrompt,
					"-</a></div><div id='div_",
					$parm->reportParameterID,
					"' style='";
				
				if(!$passedParm){
					echo "display:none;";
				}
				
				echo "float:left;margin-bottom: 5px;'><table class='noborder'><tr><td class='noborder'><select name='prm_left_",
					$parm->reportParameterID,
					"' id='prm_left_",
					$parm->reportParameterID,
					"' class='opt' size='10' multiple='multiple' style='width:175px'>";

				
				if ($parm->requiredInd != '1'){
					echo "<option value='' selected>All</option>";
				}
				
				
				$rightParms='';
				foreach ($parm->getSelectValues($_GET['prm_' . $parm->parentReportParameterID]) as $value){
					$cde = strtr( str_replace("'","\\'", $value['cde']),',',"\\");

					
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
				
				echo "</select></td>",
					
				//echo javascript left/right buttons
					"<td align='center' valign='middle' style='border:0px;'><input type='button' value='--&gt;' style='width:35px' onclick='moveOptions(this.form.prm_left_",
					$parm->reportParameterID,
					", this.form.prm_right_",
					$parm->reportParameterID,
					");placeInHidden(\",\",\"prm_right_",
					$parm->reportParameterID,
					"\", \"prm_",
					$parm->reportParameterID,
					"\");' /><br><input type='button' value='&lt;--' style='width:35px' onclick='moveOptions(this.form.prm_right_",
					$parm->reportParameterID,
					", this.form.prm_left_",
					$parm->reportParameterID,
					");placeInHidden(\",\",\"prm_right_",
					$parm->reportParameterID,
					"\", \"prm_",
					$parm->reportParameterID,
					"\");' /></td><td style='border:0px;'><select name='prm_right_",
					$parm->reportParameterID,
					"' id='prm_right_",
					$parm->reportParameterID,
					"' class='opt' size='10' multiple='multiple' style='width:175px'>",
					($rightParms?$rightParms:''),
					"</select></td></tr><tr><td style='border:0px;' colspan='3' align='left'><input type='hidden' name='prm_",
					$parm->reportParameterID,
					"' id='prm_",
					$parm->reportParameterID,
					"' value=\"",
					 $passedParm,
					"\"><a href=\"javascript:toggleLayer('div_",
					$parm->reportParameterID,
					"','none');toggleLayer('div_show_",
					$parm->reportParameterID,
					"','block');\">-Hide ",
					$parm->parameterDisplayPrompt,
					"-</a></td></tr></table></div></span>";
				
			}else if ($parm->parameterTypeCode === "chk"){

				echo "<input type='checkbox' name='prm_",
					$parm->reportParameterID,
					"' class='opt' style='text-align:left;width:13px;' ",
					(($_GET['prm_' . $parm->reportParameterID] === 'on' || $_GET['prm_' . $parm->reportParameterID] === 'Y')
						? 'checked' : ''),
					">";
			} else {
				echo "<input type='text' name='prm_",
					$parm->reportParameterID,
					"' value='' class='opt'>",
					(($parm->parameterFormatCode === 'date')
						?'<font size="-2">ex: MM/DD/YYYY</font>':'');
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
<input type="submit" value="Submit" name="submitbutton" id="submitbutton">
<input type="button" value="Reset" name="resetbutton" id="resetbutton" onclick="javascript:clearParms();">
</td>
</tr>
</table>
	
</form>
	
	
<br />
<a href="mailto:Benjamin.J.Heet.2@ND.EDU">Contact / Help</a>
</td>
</tr>
</table>
</form>
<br />
<br />


</center>
<br />
<br />

<script type="text/javascript" src="js/index.js"></script>

<?php
  //print footer
  include 'templates/footer.php';
  ob_end_flush();
?>
