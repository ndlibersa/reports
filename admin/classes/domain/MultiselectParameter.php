<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DefaultMultiselectParameter
 *
 * @author bgarcia
 */
class MultiselectParameter extends Parameter implements ParameterInterface {

    public function fetchValue() {
        if(!isset($_REQUEST["prm_$this->id"]))
            return null;

        if (!isset($_REQUEST['useHidden']) || $_REQUEST['useHidden'] == null) {
            return implode("', '", explode(',', str_replace('\\\\', ',', $_REQUEST["prm_$this->id"])));
        } else {
            return trim($_REQUEST["prm_$this->id"]);
        }
    }

    public function htmlForm() {
        $options = "";
        if ($this->requiredInd != '1') {
            $options .= "<option value='' selected='selected'>All</option>";
        }
        if (isset(Parameter::$ajax_parmValues[$this->parentReportParameterID])) {
            foreach ( $this->getSelectValues(Parameter::$ajax_parmValues[$this->parentReportParameterID]) as $value ) {
                $options .= "<option value='" . strtr($value['cde'], ",'", "\\\\") . "'>" . $value['val'] . "</option>";
            }
        }
        echo "<div id='div_parm_$this->id'>
                      <br />
                      <label for='prm_$this->id'>$this->displayPrompt</label>
<span style='margin-left:-90px'>
    <div id='div_show_$this->id' style='float:left;margin-bottom: 5px'>
        <a href=\"javascript:toggleLayer('div_$this->id','block');
           toggleLayer('div_show_$this->id','none');\">-Click to choose $this->displayPrompt-</a>
    </div>
    <div id='div_$this->id' style='display:none;float:left;margin-bottom: 5px;'>
        <table class='noborder'>
            <tr>
                <td class='noborder'>
                    <select name='prm_left_$this->id' id='prm_left_$this->id' class='opt' size='10'
                    multiple='multiple' style='width:175px'>
                        $options
                    </select>
                </td>
                <td align='center' valign='middle' style='border:0px;'>
                    <input type='button' value='--&gt;' style='width:35px'
                        onclick='moveOptions(this.form.prm_left_$this->id, this.form.prm_right_$this->id);
                        placeInHidden(\",\",\"prm_right_$this->id\", \"prm_$this->id\");'/>
                    <input type='button' value='&lt;--' style='width:35px'
                        onclick='moveOptions(this.form.prm_right_$this->id, this.form.prm_left_$this->id);
                        placeInHidden(\",\",\"prm_right_$this->id\", \"prm_$this->id\");'/>
                </td>
                <td style='border:0px;'>
                    <select name='prm_right_$this->id' id='prm_right_$this->id' class='opt' size='10' multiple='multiple' style='width:175'>
                    </select>
                </td>
            </tr>

            <tr>
                <td style='border:0px;' colspan='3' align='left'>
                    <input type='hidden' name='prm_$this->id' id='prm_$this->id' value=''/>
                    <a href=\"javascript:toggleLayer('div_$this->id','none');
                        toggleLayer('div_show_$this->id','block');\">-Hide $this->displayPrompt-</a>
                </td>
            </tr>
        </table>
    </div>
</span></div>";
    }

    public function ajax_getChildUpdate() {
        $reportParameterVal = $_GET['reportParameterVal'];
        $options = "";
        if ($this->requiredInd != '1') {
            $options .= "<option value='' selected>All</option>";
        }
        foreach ( $this->getSelectValues($reportParameterVal) as $value ) {
            $options .= "<option value='"
                . strtr(str_replace("'", "\\'", $value['cde']), ',', "\\") . "'>" . $value['val']
                . "</option>";
        }
        echo
"<span style='margin-left:-90px'>
    <div id='div_show_$this->id' style='float:left;margin-bottom: 5px'>
        <a href=\"javascript:toggleLayer('div_$this->id','block');
           toggleLayer('div_show_$this->id','none');\">-Click to choose $this->displayPrompt-</a>
    </div>
    <div id='div_$this->id' style='display:none;float:left;margin-bottom: 5px;'>
        <table class='noborder'>
            <tr>
                <td class='noborder'>
                    <select name='prm_left_$this->id' id='prm_left_$this->id' class='opt' size='10'
                    multiple='multiple' style='width:175px'>
                        $options
                    </select>
                </td>
                <td align='center' valign='middle' style='border:0px;'>
                    <input type='button' value='--&gt;' style='width:35px'
                        onclick='moveOptions(this.form.prm_left_$this->id, this.form.prm_right_$this->id);
                        placeInHidden(\",\",\"prm_right_$this->id\", \"prm_$this->id\");'/>
                    <input type='button' value='&lt;--' style='width:35px'
                        onclick='moveOptions(this.form.prm_right_$this->id, this.form.prm_left_$this->id);
                        placeInHidden(\",\",\"prm_right_$this->id\", \"prm_$this->id\");'/>
                </td>
                <td style='border:0px;'>
                    <select name='prm_right_$this->id' id='prm_right_$this->id' class='opt' size='10' multiple='multiple' style='width:175'>
                    </select>
                </td>
            </tr>

            <tr>
                <td style='border:0px;' colspan='3' align='left'>
                    <input type='hidden' name='prm_$this->id' id='prm_$this->id' value=''/>
                    <a href=\"javascript:toggleLayer('div_$this->id','none');
                        toggleLayer('div_show_$this->id','block');\">-Hide $this->displayPrompt-</a>
                </td>
            </tr>
        </table>
    </div>
</span>";
    }
}
