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
class Report {
	protected $db;
	protected $ID;
	protected $name;
	protected $dbname;
	protected $sql;
	protected $bGroupTotalInd;
	public $orderBySQL;
	protected $infoText;
	
	public function __construct($id){
        if (!isset($id)){
            die();
        } 
        $this->db = new DBService();
        $result = $this->db
            ->query("SELECT * FROM Report WHERE reportID = '$id' LIMIT 1")
            ->fetchRow(MYSQLI_ASSOC);

        $this->ID = $id;
        $this->name = $result['reportName'];
        $this->dbname = $result['reportDatabaseName'];
        $this->bGroupTotalInd = ($result['groupTotalInd'] === '1') ? true : false;
        $this->orderBySQL = $result['orderBySQL'];
        $this->infoText = $result['infoDisplayText'];
        $this->sql = $result['reportSQL'];
	}
	
	public function getID(){return $this->ID;}
	public function getName(){return $this->name;}
	public function getDBName(){return $this->dbname;}
	public function getInfoText(){return $this->infoText;}
	public function hasGroupTotalInd(){return $this->bGroupTotalInd;}
	
	public function run($archiveInd, $ADD_WHERE1, $ADD_WHERE2, $orderBy){
		if (stripos($this->sql, 'mus')) $ch = 'm';
		else $ch = 'y';
		$sql = str_replace('ADD_WHERE', "$ADD_WHERE1 AND $ch" . "us.archiveInd = $archiveInd", str_replace('ADD_WHERE2', $ADD_WHERE2, $this->sql)) . $orderBy;
		$db = new DBService(Config::$database->{$this->getDBName()});
		return $db->query($sql);
	}
	
	// returns outlier array for display at the bottom of reports
	public function getOutliers(){
		$outlier = array();
		foreach ( $this->db->changeDB(Config::$database->{$this->dbname})->query("SELECT outlierLevel, overageCount, overagePercent FROM Outlier ORDER BY 2")->fetchRows(MYSQLI_ASSOC) as $outlierArray ){
			$outlier[$outlierArray['outlierLevel']]['overageCount'] = $outlierArray['overageCount'];
			$outlier[$outlierArray['outlierLevel']]['overagePercent'] = $outlierArray['overagePercent'];
			$outlier[$outlierArray['outlierLevel']]['outlierLevel'] = $outlierArray['outlierLevel'];
		}
		return $outlier;
	}
	
	// returns associated parameters
	public function getParameters(){
		// set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);
		
		$objects = array();
		foreach ( $this->db
				->query("SELECT reportParameterID
					FROM ReportParameter
					WHERE reportID = '{$this->ID}'
					ORDER BY 1")
				->fetchRows(MYSQLI_ASSOC) as $row ){
			$objects[] = new ReportParameter($row['reportParameterID']);
		}
		return $objects;
	}
	
	// removes associated parameters
	public function getGroupingColumns(){
		// set database to reporting database name
		Config::init();
		$this->db->changeDB(Config::$database->name);
		// Get the report grouping columns into groupColsArray for faster lookup later
		// returns array of objects
		$groupColsArray = array();
		foreach ( $this->db
				->query("SELECT reportGroupingColumnName 
						FROM ReportGroupingColumn 
						WHERE reportID = '{$this->ID}'")
				->fetchRows(MYSQLI_ASSOC) as $row ){
			$groupColsArray[$row['reportGroupingColumnName']] = false;
		}
		return $groupColsArray;
	}
	
	// removes associated parameters
	public function getReportSums(){
		Config::init();
		// Get the report summing columns into sumColsArray for faster lookup later
		// returns array of objects
		$sumColsArray = array();
		foreach($this->db
				->changeDB(Config::$database->name)
				->query("SELECT reportColumnName, reportAction 
						FROM ReportSum 
						WHERE reportID = '{$this->ID}'")
				->fetchRows(MYSQLI_ASSOC) as $row ){
			$sumColsArray[$row['reportColumnName']] = $row['reportAction'];
		}
		return $sumColsArray;
	}
	
	// return the title of the ejournal for this report
	public function getUsageTitle($titleID){
		Config::init();
		$row = $this->db
			->changeDB(Config::$database->{$this->dbname})
			->query("SELECT title FROM Title WHERE titleID = '$titleID'")
            ->fetchRow(MYSQLI_ASSOC);
        return $row['title'];
	}
	
	public function printPlatformInfo(&$platforms){
		foreach ( $platforms as $platform ){
			echo "<tr valign='top'><td align='right'><b>{$platform['reportDisplayName']}</b></td><td>Year";
			if ($platform['startYear'] != '' && ($platform['endYear'] == '' || $platform['endYear'] == '0')){
				echo ": {$platform['startYear']} "._("to present");
			}else{
				echo "s: {$platform['startYear']} "._("to")." {$platform['endYear']}";
			}
			echo "</td><td>"._("This Interface ");
			if ($platform['counterCompliantInd'] == '1'){
				echo _("provides COUNTER compliant stats.").'<br>';
			}else{
				echo _("does not provide COUNTER compliant stats.").'<br>';
			}
			if ($platform['noteText']){
				echo "<br><i>"._("Interface Notes")."</i>: {$platform['noteText']}<br>";
			}
			echo '</td></tr>';
		}
	}
	
	public function printPublisherInfo(&$publishers){
		foreach ( $publishers as $publisher ){
			echo "<tr valign='top'><td align='right'><b>{$publisher['reportDisplayName']}</b></td><td>"._("Year");
			if (($publisher['startYear'] != '') && ($publisher['endYear'] == '')){
				echo ": {$publisher['startYear']}";
			}else{
				echo "s: {$publisher['startYear']} "._("to")." {$publisher['endYear']}";
			}
			echo '</td><td>';
			if (isset($publisher['notes'])){
				echo $publisher['notes'];
			}
			echo '</td></tr>';
		}
	}
}
?>
