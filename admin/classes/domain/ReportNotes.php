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
				AND pn.platformID in (" . $db->escapeString(join(',', array_keys(self::$platIDs))) . ");")->fetchRows(MYSQLI_ASSOC);
	}
	public static function publisherNotes(){
		$db = new DBService(Config::$database->{self::$dbname});
		return $db->query("SELECT startYear, endYear, noteText, reportDisplayName
				FROM PublisherPlatformNote pn, PublisherPlatform pp
				WHERE pp.publisherPlatformID = pn.publisherPlatformID
				AND pp.publisherPlatformID in (" . $db->escapeString(join(',', array_keys(self::$pubIDs))) . ");")->fetchRows(MYSQLI_ASSOC);
	}

    public static function displayNotes() {
        $header = array("Platform Interface Notes (if available)",
            "Publisher Notes (if available)");
        $dataList = array(
            ReportNotes::hasPlatforms()?ReportNotes::platformNotes():array(),
            ReportNotes::hasPublishers()?ReportNotes::publisherNotes():array()
        );

        $n = 2;
        for ($i=0; $i<$n; ++$i) {
           echo "<br/> <br/>";

           echo "<table style='border-width: 1px'>
                <tr><td colspan='3'>
                <b>$header[$i]</b>
                </td></tr>";
            foreach ( $dataList[$i] as $data ){
                echo "<tr valign='top'>
                    <td align='right'><b>{$data['reportDisplayName']}</b></td>";
                if ($data['startYear'] != '' && ($data['endYear'] == '' || $data['endYear'] == '0')){
                    echo "<td>Year: {$data['startYear']} to present</td>";
                }else{
                    echo "<td>Years: {$data['startYear']} to {$data['endYear']}</td>";
                }

                if ($i) {
                    ReportNotes::printPublisherNote($data);
                } else {
                    ReportNotes::printPlatformNote($data);
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    private static function printPlatformNote($data) {
         echo "<td>This Interface ".(($data['counterCompliantInd']=='1')?"provides":"does not provide")." COUNTER compliant stats.<br/>"
                . (($data['noteText'])?"<br/><i>Interface Notes</i>: {$data['noteText']}<br/>":'')
                . "</td>";
    }

    private static function printPublisherNote($data) {
        echo isset($data['notes'])?"<td>{$data['notes']}</td>":"";
    }
}
