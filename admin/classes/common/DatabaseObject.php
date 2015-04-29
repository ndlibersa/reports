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


class DatabaseObject extends Object {

	protected $db;

	protected $tableName;

	protected $primaryKeyName;
	protected $primaryKey;

	public $attributeNames = array();
	protected $attributes = array();

	public function __construct($primaryKey=null) {
		$this->tableName = get_class($this);
		$this->primaryKeyName = lcfirst($this->tableName) . 'ID';
		$this->primaryKey = $primaryKey;
		$this->db = new DBService;
		
		//if exists in the database
		if (isset($this->primaryKey)) {
			foreach ($this->db->processQuery(
				"SELECT * FROM `$this->tableName` WHERE `$this->primaryKeyName` = '$this->primaryKey' LIMIT 1" 
				, MYSQLI_ASSOC) as $attributeName => $row) {
				$this->attributeNames[$attributeName] = '0';
				$this->attributes[$attributeName] = $row;
			}
		}else{
			// Figure out attributes from existing database
			foreach ($this->db->processQuery("SELECT COLUMN_NAME FROM information_schema.`COLUMNS` WHERE table_schema = '"
				. Config::$database->name . "' AND table_name = '$this->tableName'") as $row) {
				$this->attributeNames[$row[0]] = '0';
			}
		}
	}
	
	public function __get($key) {
		if (isset($this->attributeNames[$key])) {
			if (!isset($this->attributes[$key])) {
				$result = $this->db->processQuery(
						"SELECT `$key` FROM `$this->tableName` WHERE `$this->primaryKeyName` = '$this->primaryKey' LIMIT 1"
					);
				if (isset($result[0])) 
					$this->attributes[$key] = stripslashes($result[0]);
			}
			return $this->attributes[$key];	
		} else if( isset($this->$key) ){
			return $this->$key;
		}
		return null;
	}
	
	public function __set($key, $value) {
		if (isset($this->attributeNames[$key])) {
			$this->attributes[$key] = $value;
		} 
		else {
			$this->$key = $value;
		}
	}
}

?>