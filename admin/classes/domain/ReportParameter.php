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

class ReportParameter extends DatabaseObject {

	//used only for allowing access to admin page
	public function getSelectValues($parentValue){
		
		//get report info so we can determine which database to use
		$parmReport = new Report($this->reportID);

		//point to the report's database
		Config::init();
		$this->db->changeDB(Config::$database->{$parmReport->reportDatabaseName});

		//if this is a restricted sql dependent on previous value
		if ($this->parameterSQLRestriction != ''){
			if ($parentValue){
				$parmSQL = str_replace("PARM", $parentValue,
					str_replace("ADD_WHERE", $this->parameterSQLRestriction, $this->parameterSQLStatement));
			}else{
				$parmSQL = str_replace("ADD_WHERE", "", $this->parameterSQLStatement);
			}
			
		}else{
			$parmSQL = str_replace("ADD_WHERE", "", $this->parameterSQLStatement);
		}

		$result = $this->db->processQuery($parmSQL);
		if ((isset($result[0])) && !isset($result[0][0]) && (!is_array($result[0]))){
			$valueArray = new SplFixedArray(1);
			$valueArray[0] = array('cde' => $result[0],'val'=>$result[1]);
		}else{
			$num_rows = count($result);
			$valueArray = new SplFixedArray($num_rows);
			for ($i=0; $i<$num_rows; ++$i) {
				$valueArray[$i] = array('cde'=>$result[$i][0], 'val'=>$result[$i][1]);
			}
		}
		
		return $valueArray;
		
		
	}

	//used only for allowing access to admin page
	public function isParent(){

		//set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);

		$result = $this->db->processQuery(
			"SELECT count(*) parent_count
				FROM ReportParameter
				WHERE parentReportParameterID = '" . $this->reportParameterID . "'",
			MYSQLI_ASSOC);

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		return ($result['parent_count']>0);
	}
	
	
	//removes associated parameters
	public function getChildren(){

		//set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);

		$result = $this->db->processQuery(
			"SELECT reportParameterID
				FROM ReportParameter
				WHERE parentReportParameterID = '" . $this->reportParameterID . "'
				ORDER BY 1",
			MYSQLI_ASSOC);

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['reportParameterID'])){
			$objects = new SplFixedArray(1);
			$objects[0] = new ReportParameter($result['reportParameterID']);
		}else{
			$num_rows = count($result);
			$objects = new SplFixedArray($num_rows);
			for ($i=0; $i<$num_rows; ++$i) {
				$objects[$i] = new ReportParameter($result[$i]['reportParameterID']);
			}
		}
		
		return $objects;
	}


	//get the display name of the publisher or platform that was sent in
	public function getPubPlatDisplayName($id){

		//get report info so we can determine which database to use
		$parmReport = new Report($this->reportID);

		//point to the report's database
		Config::init();
		$this->db->changeDB(Config::$database->{$parmReport->reportDatabaseName});

		$id = strtoupper($id);

		if (substr($id,0,2) === 'PB'){
			$result = $this->db->processQuery(
				"select distinct reportDisplayName from PublisherPlatform where concat('PB_', publisherPlatformID) in ('$id') order by 1",
				MYSQLI_ASSOC);
		}else{
			$result = $this->db->processQuery(
				"select distinct reportDisplayName from Platform where concat('PL_', platformID) in ('$id') order by 1",
				MYSQLI_ASSOC);
		}

		//need to do this since it could be that there's only one result and this is how the dbservice returns result
		
		if (isset($result['reportDisplayName'])){
			return array($result['reportDisplayName']);
		}else{
			return array_column($result,'reportDisplayName');
		}
	}
	
}

?>