<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DBResult
 *
 * @author bgarcia
 */
class DBResult {
	protected $data;
	public function __construct(mysqli_result $result){
		$this->data = $result;
	}
	public function __destruct(){
	//	$this->data->free();
	}
	public function hasData(){
		return $this->data->num_rows == 0;
	}
	public function numRows(){
		return $this->data->num_rows;
	}

	// frees sql resource after fetching single row
	public function fetchRow($type = MYSQLI_NUM){
		$row = $this->data->fetch_array($type);
		$this->data->free();
		return $row;
	}

	// sql resource not freed, used in a while loop
	public function fetchRowPersist($type = MYSQLI_NUM){
		return $this->data->fetch_array($type);
	}

	// frees sql resource after fetching all rows.
	public function fetchRows($type = MYSQLI_NUM){
		$rows = array();
		for($i = 0; $i < $this->data->num_rows; ++$i){
			$rows[$i] = $this->data->fetch_array($type);
		}
		$this->data->free();
		return $rows;
	}

	// report module specific
	public function fetchFields(){
		$fields = array();
		while ( $fld = $this->data->fetch_field() ){
			if ($fld->name === 'titleID' || $fld->name === 'platformID')
				break;
			$fields[] = $fld->name;
		}
		return $fields;
	}
}
