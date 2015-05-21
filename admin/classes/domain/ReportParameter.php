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
class ReportParameter {
	protected $db;
	public function __construct($reportParameterID = null){
		$this->db = new DBService();
		if (isset($reportParameterID)){
			$result = $this->db
				->query("SELECT * 
						FROM ReportParameter 
						WHERE reportParameterID = '$reportParameterID' LIMIT 1")
				->fetchRow(MYSQLI_ASSOC);
			$this->ID = $reportParameterID;
			$this->reportID = $result['reportID'];
			$this->displayPrompt = $result['parameterDisplayPrompt'];
			$this->addWhereClause = $result['parameterAddWhereClause'];
			$this->typeCode = $result['parameterTypeCode'];
			$this->formatCode = $result['parameterFormatCode'];
			$this->requiredInd = $result['requiredInd'];
			$this->addWhereNum = $result['parameterAddWhereNumber'];
			$this->sql = $result['parameterSQLStatement'];
			$this->parentReportParameterID = $result['parentReportParameterID'];
			$this->sqlRestriction = $result['parameterSQLRestriction'];
		}
	}
	
	// used only for allowing access to admin page
	public function getSelectValues($parentValue){
		// get report info so we can determine which database to use
		$parmReport = new Report($this->reportID);
		Config::init();
		
		// if this is a restricted sql dependent on previous value
		if ($this->sqlRestriction != ''){
			if ($parentValue){
				$parmSQL = str_replace("PARM", $parentValue, str_replace("ADD_WHERE", $this->sqlRestriction, $this->sql));
			}else{
				$parmSQL = str_replace("ADD_WHERE", "", $this->sql);
			}
		}else{
			$parmSQL = str_replace("ADD_WHERE", "", $this->sql);
		}
		$result = $this->db
			->changeDB(Config::$database->{$parmReport->getDBName()})
			->query($parmSQL)
			->fetchRows();
		$num_rows = count($result);
		$valueArray = new SplFixedArray($num_rows);
		for($i = 0; $i < $num_rows; ++$i){
			$valueArray[$i] = array('cde' => $result[$i][0],'val' => $result[$i][1]);
		}
		return $valueArray;
	}
	
	// used only for allowing access to admin page
	public function isParent(){
		// set database to reporting database name
		Config::init();
		$row = $this->db
			->changeDB(Config::$database->name)
			->query("SELECT count(*) parent_count 
					FROM ReportParameter 
					WHERE parentReportParameterID = '{$this->ID}'")
                    ->fetchRow(MYSQLI_ASSOC);
        return $row['parent_count'] > 0;
	}
	
	// removes associated parameters
	public function getChildren(){
		Config::init();
		$result = $this->db
			->changeDB(Config::$database->name)
			->query("SELECT reportParameterID 
					FROM ReportParameter 
					WHERE parentReportParameterID = '{$this->ID}' ORDER BY 1")
			->fetchRows(MYSQLI_ASSOC);
		$num_rows = count($result);
		$objects = new SplFixedArray($num_rows);
		for($i = 0; $i < $num_rows; ++$i){
			$objects[$i] = new ReportParameter($result[$i]['reportParameterID']);
		}
		return $objects;
	}
	
	// get the display name of the publisher or platform that was sent in
	public function getPubPlatDisplayName($id){
		// get report info so we can determine which database to use
		$parmReport = new Report($this->reportID);
		Config::init();
		$id = strtoupper($id);
		$sql = "select distinct reportDisplayName from ";
		if (substr($id, 0, 2) === 'PB'){
			$sql .= "PublisherPlatform where concat('PB_', publisherPlatformID)";
		}else{
			$sql .= "Platform where concat('PL_', platformID)";
		}
		$sql .= " in ('$id') order by 1";
		$result = $this->db
			->changeDB(Config::$database->{$parmReport->getDBName()})
			->query($sql)
			->fetchRows(MYSQLI_ASSOC);
		return array_column($result, 'reportDisplayName');
	}
}
?>
