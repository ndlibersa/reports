<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HiddenInputs
 *
 * @author bgarcia
 */
class HiddenInputs {
	protected $str = '';
	public function getStr(){
		return $this->str;
	}
	public function addReportID($id){
		$this->str .= '<input type="hidden" name="reportID" value="' . $id . '">';
		return $this;
	}
	public function addTitleID($id){
		$this->str .= '<input type="hidden" name="titleID" value="' . $id . '">';
		return $this;
	}
	public function addParam($paramID, $val){
		$this->str .= '<input type="hidden" name="prm_' . $paramID . '" value="' . $val . '">';
		return $this;
	}
}
