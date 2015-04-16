<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportNotes
 *
 * @author bgarcia
 */
class ReportNotes {
	public $pubIDs = array();
	public $platIDs = array();
	public $dbname;

	public function __construct($dbname) {
		$this->dbname = $dbname;
	}

	public function hasPublishers() {
		return !empty($this->pubIDs);
	}

	public function hasPlatforms() {
		return !empty($this->platIDs);
	}

	public function platformNotes() {
		$db = new DBService(Config::$database->{$this->dbname});
		return $db->processQuery2(
			"SELECT startYear, endYear, counterCompliantInd, noteText, reportDisplayName
				FROM PlatformNote pn, Platform p
				WHERE p.platformID = pn.platformID
				AND pn.platformID in ("
			. $db->sanitize(join(',', array_keys($this->platIDs)))
			. ");",
			MYSQLI_ASSOC);
	}

	public function publisherNotes() {
		$db = new DBService(Config::$database->{$this->dbname});
		return $db->processQuery2(
			"SELECT startYear, endYear, noteText, reportDisplayName
				FROM PublisherPlatformNote pn, PublisherPlatform pp
				WHERE pp.publisherPlatformID = pn.publisherPlatformID
				AND pp.publisherPlatformID in ("
			. $db->sanitize(join(',', array_keys($this->pubIDs)))
			. ");",
		MYSQLI_ASSOC);
	}
}
