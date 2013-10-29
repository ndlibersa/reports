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


class Report extends DatabaseObject {

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}


	//returns outlier array for display at the bottom of reports
	public function getOutliers(){
		$config = new Configuration();

		//set database to usage database name
		$theVarStem = "config->database->" . $this->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);


		$query = "SELECT * FROM Outlier ORDER BY 2";

		$result = $this->db->processQuery($query, 'assoc');

		$valueArray = array();

		if (empty($result)){
			return $valueArray;
		}else if (is_array($result[0])){
			foreach ($result as $row) {
				$resultArray = array();
				foreach (array_keys($row) as $attributeName) {
					$resultArray[$attributeName] = $row[$attributeName];
				}
				array_push($valueArray, $resultArray);
			}
			return $valueArray;
		}else{
			foreach (array_keys($result) as $attributeName) {
				$resultArray[$attributeName] = $result[$attributeName];
			}

			array_push($valueArray, $resultArray);
			return $valueArray;
		}

	}




	//returns associated parameters
	public function getParameters(){

		//set database to reporting database name
		$config = new Configuration();
		$this->db->changeDB($config->database->name);

		$query = "SELECT *
			FROM ReportParameter
			WHERE reportID = '" . $this->reportID . "'
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

	//removes associated parameters
	public function getGroupingColumns(){

		//set database to reporting database name
		$config = new Configuration();
		$this->db->changeDB($config->database->name);

		$query = "SELECT *
			FROM ReportGroupingColumn
			WHERE reportID = '" . $this->reportID . "'";


		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['reportID'])){
			$object = new ReportGroupingColumn(new NamedArguments(array('primaryKey' => $result['reportGroupingColumnID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new ReportGroupingColumn(new NamedArguments(array('primaryKey' => $row['reportGroupingColumnID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}

	//removes associated parameters
	public function getReportSums(){

		//set database to reporting database name
		$config = new Configuration();
		$this->db->changeDB($config->database->name);

		$query = "SELECT *
			FROM ReportSum
			WHERE reportID = '" . $this->reportID . "'";


		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['reportID'])){
			$object = new ReportSum(new NamedArguments(array('primaryKey' => $result['reportSumID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new ReportSum(new NamedArguments(array('primaryKey' => $row['reportSumID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}



	//return the title of the ejournal for this report
	public function getUsageTitle($titleID){
		$config = new Configuration();

		$theVarStem = "config->database->" . $this->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);


		$query = "SELECT title FROM Title
			WHERE titleID = '" . $titleID . "'";


		$result = $this->db->processQuery($query, 'assoc');

		return $result['title'];
	}




	//return the title of the ejournal for this report
	public function getReportResults($rprt_sql){

		//point to the report's database
		$config = new Configuration();

		$theVarStem = "config->database->" . $this->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);

		$result = $this->db->processQuery($rprt_sql, 'assoc');

		$valueArray = array();

		if (empty($result)){
			return $valueArray;
		}else if (is_array($result[0])){
			foreach ($result as $row) {
				$resultArray = array();
				foreach (array_keys($row) as $attributeName) {
					$resultArray[$attributeName] = $row[$attributeName];
				}
				array_push($valueArray, $resultArray);
			}
			return $valueArray;
		}else{
			foreach (array_keys($result) as $attributeName) {
				$resultArray[$attributeName] = $result[$attributeName];
			}

			array_push($valueArray, $resultArray);
			return $valueArray;
		}




	}




	public function getPlatformInformation($platformIDs){

		$sql = "SELECT startYear, endYear, counterCompliantInd, noteText, reportDisplayName
				FROM PlatformNote pn, Platform p
				WHERE p.platformID = pn.platformID
				AND pn.platformID in (" . $platformIDs . ");";

		$config = new Configuration();

		$theVarStem = "config->database->" . $this->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);

		$result = $this->db->processQuery($sql, 'assoc');

		$valueArray = array();

		if (empty($result)){
			return $valueArray;
		}else if (is_array($result[0])){
			foreach ($result as $row) {
				$resultArray = array();
				foreach (array_keys($row) as $attributeName) {
					$resultArray[$attributeName] = $row[$attributeName];
				}
				array_push($valueArray, $resultArray);
			}
			return $valueArray;
		}else{
			foreach (array_keys($result) as $attributeName) {
				$resultArray[$attributeName] = $result[$attributeName];
			}

			array_push($valueArray, $resultArray);
			return $valueArray;
		}




	}





	public function getPublisherInformation($publisherIDs){

		$sql = "SELECT startYear, endYear, noteText, reportDisplayName
				FROM PublisherPlatformNote pn, PublisherPlatform pp
				WHERE pp.publisherPlatformID = pn.publisherPlatformID
				AND pp.publisherPlatformID in (" . $publisherIDs . ");";

		$config = new Configuration();

		$theVarStem = "config->database->" . $this->reportDatabaseName;
		$databaseName = eval("return \$$theVarStem;");
		$this->db->changeDB($databaseName);

		$result = $this->db->processQuery($sql, 'assoc');

		$valueArray = array();

		if (empty($result)){
			return $valueArray;
		}else if (is_array($result[0])){
			foreach ($result as $row) {
				$resultArray = array();
				foreach (array_keys($row) as $attributeName) {
					$resultArray[$attributeName] = $row[$attributeName];
				}
				array_push($valueArray, $resultArray);
			}
			return $valueArray;
		}else{
			foreach (array_keys($result) as $attributeName) {
				$resultArray[$attributeName] = $result[$attributeName];
			}

			array_push($valueArray, $resultArray);
			return $valueArray;
		}




	}


}

?>
