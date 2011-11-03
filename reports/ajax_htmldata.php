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


switch ($_GET['action']) {

    case 'getReportParameters':
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
					echo "<option selected value=''>All</option>\n";
				}

				$rownumber=1;
				foreach ($parm->getSelectValues($parmValue[$parm->parentReportParameterID]) as $value){
					if (($rownumber == '1') && ($parm->requiredInd == '1')) $parmValue[$parm->reportParameterID] = $value[0];

					echo "<option value='" . $value['cde'] . "'>" . $value['val'] . "</option>\n";

					$rownumber++;
				}

				echo "</select>";

			}else if ($parm->parameterTypeCode == "ms"){
                  echo "<br />";
                  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
                  echo "<span style='margin-left:-90px'>";
                  echo "<div id='div_show_" . $parm->reportParameterID . "' style='float:left;margin-bottom: 5px'>";
                  echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','block');toggleLayer('div_show_" . $parm->reportParameterID . "','none');\">-Click to choose " . $parm->parameterDisplayPrompt . "-</a>";
                  echo "</div>";
                  echo "<div id='div_" . $parm->reportParameterID . "' style='display:none;float:left;margin-bottom: 5px;'>";
                  echo "<table class='noborder'> <tr> <td class='noborder'>";
                  echo "<select name='prm_left_" . $parm->reportParameterID . "' id='prm_left_" . $parm->reportParameterID . "' class='optionStyle' size='10' multiple='multiple' style='width:175px'>\n";

                  if ($parm->requiredInd != '1'){
                  	echo "<option selected value=''>All</option>\n";
                  }

		  		  foreach ($parm->getSelectValues($parmValue[$parm->parentReportParameterID]) as $value){
		  				$cde = str_replace("'","\\'", $value['cde']);
		  				$cde = str_replace(",","\\", $value['cde']);

                        echo "<option value='" . $cde . "'>" . $value['val'] . "</option>\n";
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
                  echo "</select>\n";
                  echo "</td></tr><tr><td style='border:0px;' colspan='3' align='left'>\n";
                  echo "<input type='hidden' name='prm_" . $parm->reportParameterID . "' id='prm_" . $parm->reportParameterID . "' value=\"\">";
                  echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','none');toggleLayer('div_show_" . $parm->reportParameterID . "','block');\">-Hide " . $parm->parameterDisplayPrompt . "-</a>";
                  echo "</td></tr></table>\n";
                  echo "</div>";
                  echo "</span>";

			}else if ($parm->parameterTypeCode == "chk"){

                  echo "<br />";
                  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
                  echo "<input type='checkbox' name='prm_" . $parm->reportParameterID . "' class='optionStyle' style='text-align:left;width:13px;'>\n";

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




        break;



    case 'getChildParameters':
		$parentID = $_GET['parentReportParameterID'];

    	$reportParameter = new ReportParameter(new NamedArguments(array('primaryKey' => $parentID)));

		$parmArray = array();
		foreach ($reportParameter->getChildren() as $parm) {
			echo $parm->reportParameterID . "|";
		}


        break;





    case 'getChildUpdate':
		$reportParameterID = $_GET['reportParameterID'];
		$reportParameterVal = $_GET['reportParameterVal'];

    	$parm = new ReportParameter(new NamedArguments(array('primaryKey' => $reportParameterID)));

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
				echo "<option selected value=''>All</option>\n";
			}

			$rownumber=1;
			foreach ($parm->getSelectValues($reportParameterVal) as $value){
				if (($rownumber == '1') && ($parm->requiredInd == '1')) $parmValue[$parm->reportParameterID] = $value[0];

				echo "<option value='" . $value['cde'] . "'>" . $value['val'] . "</option>\n";

				$rownumber++;
			}

			echo "</select>";

		}else if ($parm->parameterTypeCode == "ms"){
			  echo "<br />";
			  echo "<label for='prm_" . $parm->reportParameterID . "'>" . $parm->parameterDisplayPrompt . "</label>\n";
			  echo "<span style='margin-left:-90px'>";
			  echo "<div id='div_show_" . $parm->reportParameterID . "' style='float:left;margin-bottom: 5px'>";
			  echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','block');toggleLayer('div_show_" . $parm->reportParameterID . "','none');\">-Click to choose " . $parm->parameterDisplayPrompt . "-</a>";
			  echo "</div>";
			  echo "<div id='div_" . $parm->reportParameterID . "' style='display:none;float:left;margin-bottom: 5px;'>";
			  echo "<table class='noborder'> <tr> <td class='noborder'>";
			  echo "<select name='prm_left_" . $parm->reportParameterID . "' id='prm_left_" . $parm->reportParameterID . "' class='optionStyle' size='10' multiple='multiple' style='width:175px'>\n";

			  if ($parm->requiredInd != '1'){
				echo "<option selected value=''>All</option>\n";
			  }

			  foreach ($parm->getSelectValues($reportParameterVal) as $value){
					$cde = str_replace("'","\\'", $value['cde']);
					$cde = str_replace(",","\\", $cde);

					echo "<option value='" . $cde . "'>" . $value['val'] . "</option>\n";
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
			  echo "</select>\n";
			  echo "</td></tr><tr><td style='border:0px;' colspan='3' align='left'>\n";
			  echo "<input type='hidden' name='prm_" . $parm->reportParameterID . "' id='prm_" . $parm->reportParameterID . "' value=\"\">";
			  echo "<a href=\"javascript:toggleLayer('div_" . $parm->reportParameterID . "','none');toggleLayer('div_show_" . $parm->reportParameterID . "','block');\">-Hide " . $parm->parameterDisplayPrompt . "-</a>";
			  echo "</td></tr></table>\n";
			  echo "</div>";
			  echo "</span>";
		}


        break;




	default:
       echo "Action " . $action . " not set up!";
       break;


}


?>

