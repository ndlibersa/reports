<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportResult
 *
 * @author bgarcia
 */
class ReportResult {
	public $data;
	public $fields = array();
	public $numFields;

	public function __construct($dbname,$sql) {
		$this->sql = $sql;

		Config::init();

		$this->db = new mysqli(
			Config::$database->host,
			Config::$database->username,
			Config::$database->password
			);
		if ($this->db->errno) {
			throw new Exception(_("There was a problem with the database: ") . $this->db->error);
		} else if( ! ($this->db->select_db(Config::$database->$dbname)) ) {
			throw new Exception(_("There was a problem with the database: ") . $this->db->error);
		} else if(! ($this->data = $this->db->query($this->sql,MYSQLI_USE_RESULT)) ) {
			throw new Exception(_("There was a problem with the database: ") . $this->db->error);
		}

		while($fld = $this->data->fetch_field()){
			if($fld->name === 'titleID' || $fld->name === 'platformID')
				break;
			$this->fields[] = $fld->name;
		}
		$this->numFields = count($this->fields);
	}

	public function __destruct() {
		if($this->data)
			$this->data->free();
	}
}
