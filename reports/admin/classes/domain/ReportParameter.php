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

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}


	//used only for allowing access to admin page
	public function getSelectValues($parentValue){

		//get report info so we can determine which database to use
		$parmReport = new Report(new NamedArguments(array('primaryKey' => $this->reportID)));

		//point to the report's database
		$config = new Configuration();

		$theVarStem = "config->database->" . $parmReport->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);

		//if this is a restricted sql dependent on previous value
		if ($this->parameterSQLRestriction != ''){
			if ($parentValue){
				$parmSQL = str_replace("ADD_WHERE", $this->parameterSQLRestriction, $this->parameterSQLStatement);
				$parmSQL = str_replace("PARM", $parentValue, $parmSQL);
			}else{
				$parmSQL = str_replace("ADD_WHERE", "", $this->parameterSQLStatement);
			}

		}else{
			$parmSQL = str_replace("ADD_WHERE", "", $this->parameterSQLStatement);
		}

		$result = $this->db->processQuery($parmSQL);

		$valueArray = array();

		//need to do this since it could be that there's only one result and this is how the dbservice returns result

		if ((isset($result[0])) && (!is_array($result[0]))){
			$resultArray = array();
			$resultArray['cde'] = $result[0];
			$resultArray['val'] = $result[1];

			array_push($valueArray, $resultArray);
		}else{
			foreach ($result as $row) {
				$resultArray = array();
				$resultArray['cde'] = $row[0];
				$resultArray['val'] = $row[1];

				array_push($valueArray, $resultArray);
			}
		}

		return $valueArray;


	}

	//used only for allowing access to admin page
	public function isParent(){

		//set database to reporting database name
		$config = new Configuration();
		$this->db->changeDB($config->database->name);


		$query = "SELECT count(*) parent_count
			FROM ReportParameter
			WHERE parentReportParameterID = '" . $this->reportParameterID . "'";


		$result = $this->db->processQuery($query, 'assoc');



		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if ($result['parent_count'] > 0){
			return true;
		}else{
			return false;
		}


	}



	//removes associated parameters
	public function getChildren(){

		//set database to reporting database name
		$config = new Configuration();
		$this->db->changeDB($config->database->name);


		$query = "SELECT *
			FROM ReportParameter
			WHERE parentReportParameterID = '" . $this->reportParameterID . "'
			ORDER BY 1";


		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['reportParameterID'])){
			$object = new ReportParameter(new NamedArguments(array('primaryKey' => $result['reportParameterID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new ReportParameter(new NamedArguments(array('primaryKey' => $row['reportParameterID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}


	//get the display name of the publisher or platform that was sent in
	public function getPubPlatDisplayName($id){

		//get report info so we can determine which database to use
		$parmReport = new Report(new NamedArguments(array('primaryKey' => $this->reportID)));

		//point to the report's database
		$config = new Configuration();

		$theVarStem = "config->database->" . $parmReport->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);


		$id = strtoupper($id);

		if (substr($id,0,2) == 'PB'){
			$query = "select distinct reportDisplayName from PublisherPlatform where concat('PB_', publisherPlatformID) in ('$id') order by 1";
		}else{
			$query = "select distinct reportDisplayName from Platform where concat('PL_', platformID) in ('$id') order by 1";
		}

		$result = $this->db->processQuery($query, 'assoc');

		$valueArray = array();

		//need to do this since it could be that there's only one result and this is how the dbservice returns result

		if (isset($result['reportDisplayName'])){
			$valueArray[] = $result['reportDisplayName'];

		}else{
			foreach ($result as $row) {
				$valueArray[] = $row['reportDisplayName'];
			}
		}

		return $valueArray;


	}




}

?>
