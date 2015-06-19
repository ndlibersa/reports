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
	protected static $pubIDs;
	protected static $platIDs;
	protected static $dbname;

    public static function init($dbname){
		self::$dbname = $dbname;
        self::$pubIDs = array();
        self::$platIDs = array();
	}

	public static function addPublisher($id){
		self::$pubIDs[$id] = '';
	}
	public static function addPlatform($id){
		self::$platIDs[$id] = '';
	}
	public static function hasPublishers(){
		return !empty(self::$pubIDs);
	}
	public static function hasPlatforms(){
		return !empty(self::$platIDs);
	}
	public static function platformNotes(){
		$db = new DBService(Config::$database->{self::$dbname});
		return $db->query("SELECT startYear, endYear, counterCompliantInd, noteText, reportDisplayName
				FROM PlatformNote pn, Platform p
				WHERE p.platformID = pn.platformID
				AND pn.platformID in (" . $db->sanitize(join(',', array_keys(self::$platIDs))) . ");")->fetchRows(MYSQLI_ASSOC);
	}
	public static function publisherNotes(){
		$db = new DBService(Config::$database->{self::$dbname});
		return $db->query("SELECT startYear, endYear, noteText, reportDisplayName
				FROM PublisherPlatformNote pn, PublisherPlatform pp
				WHERE pp.publisherPlatformID = pn.publisherPlatformID
				AND pp.publisherPlatformID in (" . $db->sanitize(join(',', array_keys(self::$pubIDs))) . ");")->fetchRows(MYSQLI_ASSOC);
	}
}
