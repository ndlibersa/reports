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
class DBService {
	protected static $db = null;
	protected $error;
	public function __construct($dbname = null){
		Config::init();
		if (!self::$db && !(self::$db = new mysqli(Config::$database->host, Config::$database->username, Config::$database->password))){
			throw new RuntimeException("There was a problem with the database: " . self::$db->error);
		}else if ($dbname){
			if (!self::$db->select_db($dbname)){
				throw new RuntimeException("There was a problem with the database: " . self::$db->error);
			}
		}else if (!(self::$db->select_db(Config::$database->name))){
			throw new RuntimeException("There was a problem with the database: " . self::$db->error);
		}

		if ($dbname)
			$this->selectDB($dbname);
	}
	public function selectDB($databaseName){
		// $databaseName='coral_reporting_pprd';
		if (!self::$db->select_db($databaseName)){
			throw new RuntimeException("There was a problem with the database: " . self::$db->error);
		}
		return $this;
	}
	public static function getDatabase(){
		return self::$db;
	}
	public static function query($sql){
		if (!($result = self::$db->query($sql))) {
			throw new RuntimeException("There was a problem with the database: " . self::$db->error);
        } else if ($result instanceof mysqli_result){
			return new DBResult($result);
		}else if ($result){
			return self::$db->insert_id;
		}
		return array();
	}
	public static function escapeString($str){
		return self::$db->real_escape_string($str);
	}
}

?>