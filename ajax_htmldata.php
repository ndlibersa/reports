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
    $report = new Report($_GET['reportID']);

    // get parameters
    $parmValue = array();

    foreach ( $report->getParameters() as $parm ) {
        if ($parm->typeCode === 'dddr') {
            DateRange::PrintForm($parm);
            continue;
        } else {
            $div_parm_contents = "";
            if ($parm->typeCode === "dd") {
                $options = "";

                if ($parm->requiredInd != '1') {
                    $options .= "<option value='' selected>all</option>";
                }
                $rownumber = 1;
                if (isset($parmValue[$parm->parentReportParameterID]))
                    $p = $parmValue[$parm->parentReportParameterID];
                else
                    $p = null;
                foreach ( $parm->getSelectValues($p) as $value ) {
                    if (($rownumber === '1') && ($parm->requiredInd == '1'))
                        $parmValue[$parm->ID] = $value[0];
                    $options .= "<option value='{$value['cde']}'>" . $value['val'] . "</option>";
                    ++$rownumber;
                }
                $div_parm_contents .= "<select name='prm_$parm->ID' id='prm_$parm->ID' class='opt' ";
                if ($parm->isParent()) {
                    $div_parm_contents .= "onchange='javascript:updateChildren($parm->ID);";
                }
                $div_parm_contents .= ">$options</select>";
            } else if ($parm->typeCode === "ms") {
                $options = "";
                if ($parm->requiredInd != '1') {
                    $options .= "<option value='' selected>All</option>";
                }
                if (isset($parmValue[$parm->parentReportParameterID])) {
                    foreach ( $parm->getSelectValues($parmValue[$parm->parentReportParameterID]) as $value ) {
                        $options .= "<option value='" . strtr($value['cde'], ",'", "\\\\") . "'>" . $value['val'] . "</option>";
                    }
                }
                $div_parm_contents .=
"<span style='margin-left:-90px'>
    <div id='div_show_$parm->ID' style='float:left;margin-bottom: 5px'>
        <a href=\"javascript:toggleLayer('div_$parm->ID','block');
           toggleLayer('div_show_$parm->ID','none');\">-Click to choose $parm->displayPrompt-</a>
    </div>
    <div id='div_$parm->ID' style='display:none;float:left;margin-bottom: 5px;'>
        <table class='noborder'>
            <tr>
                <td class='noborder'>
                    <select name='prm_left_$parm->ID' id='prm_left_$parm->ID' class='opt' size='10'
                        multiple='multiple' style='width:175px'>
                        $options
                    </select>
                </td>
                <td align='center' valign='middle' style='border:0px;'>
                    <input type='button' value='--&gt;' style='width:35px'
                        onclick='moveOptions(this.form.prm_left_$parm->ID, this.form.prm_right_$parm->ID);
                        placeInHidden(\",\",\"prm_right_$parm->ID\", \"prm_$parm->ID\");'/>
                    <input type='button' value='&lt;--' style='width:35px'
                        onclick='moveOptions(this.form.prm_right_$parm->ID, this.form.prm_left_$parm->ID);
                        placeInHidden(\",\",\"prm_right_$parm->ID\", \"prm_$parm->ID\");'/>
                </td>
                <td style='border:0px;'>
                    <select name='prm_right_$parm->ID' id='prm_right_$parm->ID' class='opt'
                        size='10' multiple='multiple' style='width:175'>
                    </select>
                </td>
            </tr>
            <tr>
                <td style='border:0px;' colspan='3' align='left'>
                    <input type='hidden' name='prm_$parm->ID' id='prm_$parm->ID' value=''/>
                    <a href=\"javascript:toggleLayer('div_$parm->ID','none');
                        toggleLayer('div_show_$parm->ID','block');\">-Hide $parm->displayPrompt-</a>
                </td>
            </tr>
        </table>
    </div>
</span>";
            } else if ($parm->typeCode === "chk") {
                $div_parm_contents .= "<input type='checkbox' name='prm_$parm->ID' class='opt'
                    style='text-align:left;width:13px;'/>";
            } else {
                $div_parm_contents .= "<input type='text' name='prm_$parm->ID' value='' class='opt'/>"
                    . (($parm->formatCode === 'date') ? '<font size="-2">ex: MM/DD/YYYY</font>' : '');
            }
            echo "<div id='div_parm_$parm->ID'>
                      <br/>
                      <label for='prm_$parm->ID'>$parm->displayPrompt</label>
                      $div_parm_contents
                  </div>";
        }
    }
} else if ($action === 'getChildParameters') {
    $reportParameter = new ReportParameter($_GET['parentReportParameterID']);
    $parmArray = array();
    foreach ( $reportParameter->getChildren() as $parm ) {
        echo $parm->ID . "|";
    }
} else if ($action === 'getChildUpdate') {
    $reportParameterVal = $_GET['reportParameterVal'];
    $parm = new ReportParameter($_GET['reportParameterID']);
    echo "<br/><label for='prm_$parm->ID'>$parm->displayPrompt</label>";
    if ($parm->typeCode === "dd") {
        echo "<select name='prm_$parm->ID' id='prm_$parm->ID' class='opt' ";
        // check if it's a parent
        if ($parm->isParent()) {
            echo "onchange='javascript:updateChildren($parm->ID);'>";
        } else {
            echo ">";
        }
        if ($parm->requiredInd != '1') {
            echo "<option value='' selected>All</option>";
        }

        $rownumber = 1;
        foreach ( $parm->getSelectValues($reportParameterVal) as $value ) {
            if (($rownumber === 1) && ($parm->requiredInd == '1'))
                $parmValue[$parm->ID] = $value[0];
            echo "<option value='{$value['cde']}'>" . $value['val'] . "</option>";
            ++$rownumber;
        }

        echo "</select>";
    } else if ($parm->typeCode === "ms") {
        $options = "";
        if ($parm->requiredInd != '1') {
            $options .= "<option value='' selected>All</option>";
        }
        foreach ( $parm->getSelectValues($reportParameterVal) as $value ) {
            $options .= "<option value='"
                . strtr(str_replace("'", "\\'", $value['cde']), ',', "\\") . "'>" . $value['val']
                . "</option>";
        }
        echo
"<span style='margin-left:-90px'>
    <div id='div_show_$parm->ID' style='float:left;margin-bottom: 5px'>
        <a href=\"javascript:toggleLayer('div_$parm->ID','block');
           toggleLayer('div_show_$parm->ID','none');\">-Click to choose $parm->displayPrompt-</a>
    </div>
    <div id='div_$parm->ID' style='display:none;float:left;margin-bottom: 5px;'>
        <table class='noborder'>
            <tr>
                <td class='noborder'>
                    <select name='prm_left_$parm->ID' id='prm_left_$parm->ID' class='opt' size='10'
                    multiple='multiple' style='width:175px'>
                        $options
                    </select>
                </td>
                <td align='center' valign='middle' style='border:0px;'>
                    <input type='button' value='--&gt;' style='width:35px'
                        onclick='moveOptions(this.form.prm_left_$parm->ID, this.form.prm_right_$parm->ID);
                        placeInHidden(\",\",\"prm_right_$parm->ID\", \"prm_$parm->ID\");'/>
                    <input type='button' value='&lt;--' style='width:35px'
                        onclick='moveOptions(this.form.prm_right_$parm->ID, this.form.prm_left_$parm->ID);
                        placeInHidden(\",\",\"prm_right_$parm->ID\", \"prm_$parm->ID\");'/>
                </td>
                <td style='border:0px;'>
                    <select name='prm_right_$parm->ID' id='prm_right_$parm->ID' class='opt' size='10' multiple='multiple' style='width:175'>
                    </select>
                </td>
            </tr>

            <tr>
                <td style='border:0px;' colspan='3' align='left'>
                    <input type='hidden' name='prm_$parm->ID' id='prm_$parm->ID' value=''/>
                    <a href=\"javascript:toggleLayer('div_$parm->ID','none');
                        toggleLayer('div_show_$parm->ID','block');\">-Hide $parm->displayPrompt-</a>
                </td>
            </tr>
        </table>
    </div>
</span>";
    }
} else {
    echo "Action $action not set up!";
}

ob_end_flush();
?>

