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


include_once 'directory.php';

ob_start();
$action = $_GET['action'];

if($action === 'getReportParameters'){
	$report = new Report($_GET['reportID']);

	//get parameters
	$parmValue = array();

	foreach ($report->getParameters() as $parm) {
		echo "<div id='div_parm_"
			. $parm->reportParameterID
			. "'>";

		if ($parm->parameterTypeCode === "dd") {
			echo "<br><label for='prm_"
				. $parm->reportParameterID
				. "'>" . $parm->parameterDisplayPrompt
				. "</label><select name='prm_"
				. $parm->reportParameterID
				. "' id='prm_"
				. $parm->reportParameterID
				. "' class='opt' ";
			//check if it's a parent
			if ($parm->isParent()){
				echo "onchange='javascript:updateChildren("
					. $parm->reportParameterID . ");'>";
			}else{
				echo ">";
			}

			if ($parm->requiredInd != '1'){
				echo "<option value='' selected>all</option>";
			}
			$rownumber=1;

			if(isset($parmValue[$parm->parentReportParameterID]))
				$p = $parmValue[$parm->parentReportParameterID];
			else
				$p = null;
			foreach ($parm->getSelectValues($p) as $value){
				if (($rownumber === '1') && ($parm->requiredInd == '1'))
					$parmValue[$parm->reportParameterID] = $value[0];
				echo "<option value='" . $value['cde'] . "'>" . $value['val'] . "</option>";
				++$rownumber;
			}
			unset($p);
			echo '</select>';
		} else if ($parm->parameterTypeCode === "ms"){
			echo "<br><label for='prm_"
				. $parm->reportParameterID
				. "'>" . $parm->parameterDisplayPrompt
				. "</label><span style='margin-left:-90px'><div id='div_show_"
				. $parm->reportParameterID
				. "' style='float:left;margin-bottom: 5px'><a href=\"javascript:toggleLayer('div_"
				. $parm->reportParameterID
				. "','block');toggleLayer('div_show_"
				. $parm->reportParameterID
				. "','none');\">-Click to choose "
				. $parm->parameterDisplayPrompt
				. "-</a></div><div id='div_"
				. $parm->reportParameterID
				. "' style='display:none;float:left;margin-bottom: 5px;'><table class='noborder'><tr><td class='noborder'><select name='prm_left_"
				. $parm->reportParameterID
				. "' id='prm_left_"
				. $parm->reportParameterID
				. "' class='opt' size='10' multiple='multiple' style='width:175px'>";

			if ($parm->requiredInd != '1'){
				echo "<option value='' selected>All</option>";
			}

			if(isset($parmValue[$parm->parentReportParameterID])){
				foreach ($parm->getSelectValues($parmValue[$parm->parentReportParameterID]) as $value){
					echo "<option value='" . strtr($value['cde'],",'","\\\\") . "'>" . $value['val'] . "</option>";
				}
			}

			//echo javascript left/right buttons
			echo "</select></td><td align='center' valign='middle' style='border:0px;'><input type='button' value='--&gt;' style='width:35px' onclick='moveOptions(this.form.prm_left_"
				. $parm->reportParameterID
				. ", this.form.prm_right_"
				. $parm->reportParameterID
				. ");placeInHidden(\",\",\"prm_right_"
				. $parm->reportParameterID
				. "\", \"prm_"
				. $parm->reportParameterID
				. "\");' /><br> <input type='button' value='&lt;--' style='width:35px'   onclick='moveOptions(this.form.prm_right_"
				. $parm->reportParameterID
				. ", this.form.prm_left_"
				. $parm->reportParameterID
				. ");placeInHidden(\",\",\"prm_right_"
				. $parm->reportParameterID
				. "\", \"prm_"
				. $parm->reportParameterID
				. "\");' /></td><td style='border:0px;'><select name='prm_right_"
				. $parm->reportParameterID
				. "' id='prm_right_"
				. $parm->reportParameterID
				. "' class='opt' size='10' multiple='multiple' style='width:175px'></select></td></tr><tr><td style='border:0px;' colspan='3' align='left'><input type='hidden' name='prm_"
				. $parm->reportParameterID
				. "' id='prm_"
				. $parm->reportParameterID
				. "' value=\"\"><a href=\"javascript:toggleLayer('div_"
				. $parm->reportParameterID
				. "','none');toggleLayer('div_show_"
				. $parm->reportParameterID
				. "','block');\">-Hide "
				. $parm->parameterDisplayPrompt
				. "-</a></td></tr></table></div></span>";
		} else if ($parm->parameterTypeCode === "chk") {

			echo "<br><label for='prm_"
				. $parm->reportParameterID
				. "'>" . $parm->parameterDisplayPrompt
				. "</label><input type='checkbox' name='prm_"
				. $parm->reportParameterID
				. "' class='opt' style='text-align:left;width:13px;'>";
		} else {
			echo "<br><label for='prm_"
				. $parm->reportParameterID
				. "'>" . $parm->parameterDisplayPrompt
				. "</label><input type='text' name='prm_"
				. $parm->reportParameterID
				. "' value='' class='opt'>"
				. (($parm->parameterFormatCode === 'date')
					?'<font size="-2">ex: MM/DD/YYYY</font>':'');
		}
		echo "</div>";
		
		
	}
} else if ($action === 'getChildParameters') {
	$reportParameter = new ReportParameter($_GET['parentReportParameterID']);
	$parmArray = array();
	foreach ($reportParameter->getChildren() as $parm) {
		echo $parm->reportParameterID . "|";
	}
} else if ($action === 'getChildUpdate'){
	$reportParameterVal = $_GET['reportParameterVal'];
	$parm = new ReportParameter($_GET['reportParameterID']);
	if ($parm->parameterTypeCode === "dd"){
		echo "<br><label for='prm_"
			. $parm->reportParameterID
			. "'>" . $parm->parameterDisplayPrompt
			. "</label><select name='prm_"
			. $parm->reportParameterID
			. "' id='prm_"
			. $parm->reportParameterID
			. "' class='opt' ";
		//check if it's a parent
		if ($parm->isParent()){
			echo "onchange='javascript:updateChildren("
				. $parm->reportParameterID
				. ");'>";
		}else{
			echo ">";
		}
		if ($parm->requiredInd != '1'){
			echo "<option value='' selected>All</option>";
		}

		$rownumber=1;
		foreach ($parm->getSelectValues($reportParameterVal) as $value){
			if (($rownumber === 1) && ($parm->requiredInd == '1'))
				$parmValue[$parm->reportParameterID] = $value[0];
			echo "<option value='" . $value['cde'] . "'>" . $value['val'] . "</option>";
			++$rownumber;
		}
		
		echo "</select>";
	} else if ($parm->parameterTypeCode === "ms") {
		echo "<br><label for='prm_"
			. $parm->reportParameterID
			. "'>" . $parm->parameterDisplayPrompt
			. "</label><span style='margin-left:-90px'><div id='div_show_"
			. $parm->reportParameterID
			. "' style='float:left;margin-bottom: 5px'><a href=\"javascript:toggleLayer('div_"
			. $parm->reportParameterID
			. "','block');toggleLayer('div_show_"
			. $parm->reportParameterID
			. "','none');\">-Click to choose "
			. $parm->parameterDisplayPrompt
			. "-</a></div><div id='div_"
			. $parm->reportParameterID
			. "' style='display:none;float:left;margin-bottom: 5px;'><table class='noborder'><tr><td class='noborder'><select name='prm_left_"
			. $parm->reportParameterID
			. "' id='prm_left_"
			. $parm->reportParameterID
			. "' class='opt' size='10' multiple='multiple' style='width:175px'>";

		if ($parm->requiredInd != '1'){
		  echo "<option value='' selected>All</option>";
		}

		foreach ($parm->getSelectValues($reportParameterVal) as $value){
			echo "<option value='" . strtr(str_replace("'","\\'", $value['cde']),',',"\\" ) . "'>" . $value['val'] . "</option>";
		}

		//echo javascript left/right buttons
		echo "</select></td><td align='center' valign='middle' style='border:0px;'><input type='button' value='--&gt;' style='width:35px' onclick='moveOptions(this.form.prm_left_"
			. $parm->reportParameterID
			. ", this.form.prm_right_"
			. $parm->reportParameterID
			. ");placeInHidden(\",\",\"prm_right_"
			. $parm->reportParameterID
			. "\", \"prm_"
			. $parm->reportParameterID
			. "\");' /><br> <input type='button' value='&lt;--' style='width:35px'   onclick='moveOptions(this.form.prm_right_"
			. $parm->reportParameterID
			. ", this.form.prm_left_"
			. $parm->reportParameterID
			. ");placeInHidden(\",\",\"prm_right_"
			. $parm->reportParameterID
			. "\", \"prm_"
			. $parm->reportParameterID
			. "\");' /></td><td style='border:0px;'><select name='prm_right_"
			. $parm->reportParameterID
			. "' id='prm_right_"
			. $parm->reportParameterID
			. "' class='opt' size='10' multiple='multiple' style='width:175px'></select></td></tr><tr><td style='border:0px;' colspan='3' align='left'><input type='hidden' name='prm_"
			. $parm->reportParameterID
			. "' id='prm_"
			. $parm->reportParameterID
			. "' value=\"\"><a href=\"javascript:toggleLayer('div_"
			. $parm->reportParameterID
			. "','none');toggleLayer('div_show_"
			. $parm->reportParameterID
			. "','block');\">-Hide "
			. $parm->parameterDisplayPrompt
			. "-</a></td></tr></table></div></span>";
	}
} else{
   echo "Action " . $action . " not set up!";
}

ob_end_flush();
?>

