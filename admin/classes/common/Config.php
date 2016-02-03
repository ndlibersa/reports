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
class Config {
	public static $database;
	public static $settings;
	protected static $bInit = null;
	public static function init(){
		if (!isset(self::$bInit)){
			$data = parse_ini_file(BASE_DIR . '/admin/configuration.ini', true);
			self::$database = ( object ) $data['database'];
			self::$settings = ( object ) $data['settings'];
			self::$bInit = 'y';
		}
	}
}