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

	
	
	
	//returns outlier array for display at the bottom of reports
	public function getOutliers(){
		$this->db->changeDB(Config::$database->{$this->reportDatabaseName});
		$outlier = array();
		foreach($this->db->processQuery2(
			"SELECT outlierLevel, overageCount, overagePercent FROM Outlier ORDER BY 2",
			MYSQLI_ASSOC) as $outlierArray) {
			$outlier[$outlierArray['outlierLevel']]['overageCount'] = $outlierArray['overageCount'];
			$outlier[$outlierArray['outlierLevel']]['overagePercent'] = $outlierArray['overagePercent'];
			$outlier[$outlierArray['outlierLevel']]['outlierLevel'] = $outlierArray['outlierLevel'];
		}
		return $outlier;
	}

	
	
	
	
	
	//returns associated parameters
	public function getParameters(){

		//set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);

		$result = $this->db->processQuery(
			"SELECT reportParameterID
				FROM ReportParameter
				WHERE reportID = '" . $this->reportID . "'
				ORDER BY 1",
			MYSQLI_ASSOC
				);

		if (isset($result['reportParameterID'])){
			return array(
				new ReportParameter($result['reportParameterID']));
		}else{
			$objects = array();
			foreach ($result as $row) {
				$objects[] =
					new ReportParameter($row['reportParameterID']);
			}
			return $objects;
		}
		
	}

	//removes associated parameters
	public function getGroupingColumns(){
		
		//set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);

		$result = $this->db->processQuery(
					"SELECT reportGroupingColumnName
						FROM ReportGroupingColumn
						WHERE reportID = '" . $this->reportID . "'",
					MYSQLI_ASSOC);

		// Get the report grouping columns into groupColsArray for faster lookup later
		//returns array of objects
		if (isset($result['reportGroupingColumnName'])){
			return array($result['reportGroupingColumnName'] => false);
		}else{
			$groupColsArray = array();
			foreach ($result as $row) {
				$groupColsArray[$row['reportGroupingColumnName']] = false;
			}
			return $groupColsArray;
		}
		
	}

	//removes associated parameters
	public function getReportSums(){
		
		//set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);

		$result = $this->db->processQuery(
					"SELECT reportColumnName, reportAction
						FROM ReportSum
						WHERE reportID = '" . $this->reportID . "'",
					MYSQLI_ASSOC);

		// Get the report summing columns into sumColsArray for faster lookup later
		//returns array of objects
		if (isset($result['reportColumnName'])){
			return array($result['reportColumnName'] => $result['reportAction']);
		}else{
			$sumColsArray = array();
			foreach ($result as $row) {
				$sumColsArray[$row['reportColumnName']] = $row['reportAction'];
			}
			return $sumColsArray;
		}
		
	}

	
	
	//return the title of the ejournal for this report
	public function getUsageTitle($titleID){
		Config::init();
		$this->db->changeDB(Config::$database->{$this->reportDatabaseName});
		$result = $this->db->processQuery(
					"SELECT title
						FROM Title
						WHERE titleID = '" . $titleID . "'",
					MYSQLI_ASSOC
				);
		return $result['title'];
	}

	public function printPlatformInfo(&$platforms){
		foreach ($platforms as $platform) {
			echo'<tr valign="top"><td align="right"><b>'
				. $platform['reportDisplayName']
				. '</b></td><td>Year';

			if ($platform['startYear'] != '' &&($platform['endYear'] == '' || $platform['endYear'] == '0')){
				echo ': ' . $platform['startYear'] . ' to present';
			} else {
				echo 's: ' . $platform['startYear'] . ' to ' . $platform['endYear'];
			}
			echo '</td><td>This Interface ';

			if($platform['counterCompliantInd'] == '1'){
				echo 'provides';
			} else {
				echo 'does not provide';
			}
			echo ' COUNTER compliant stats.<br>';

			if ($platform['noteText']){
				echo '<br><i>Interface Notes</i>: '
					. $platform['noteText'] . '<br>';
			}
			echo '</td></tr>';
		}
	}

	public function printPublisherInfo(&$publishers){
		foreach ($publishers as $publisher) {
			echo '<tr valign="top"><td align="right"><b>'
				. $publisher['reportDisplayName']
				. '</b></td><td>Year';

				if (($publisher['startYear']!='')&&($publisher['endYear']=='')){
					echo ': ' . $publisher['startYear'];
				} else {
					echo 's: ' . $publisher['startYear'] . ' to ' . $publisher['endYear'];
				}
				echo '</td><td>';
			if(isset($publisher['notes'])){
				echo $publisher['notes'];
			}
			echo '</td></tr>';
		}
		
		
		
		
		
	}
	
	
}

?>