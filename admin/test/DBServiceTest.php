<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DBServiceTest
 *
 * @author bgarcia
 */
class DBServiceTest extends PHPUnit_Framework_TestCase {

    public function testConnection() {
        try {
            $dbservice = new DBService();
        } catch (RuntimeException $exception) {
            $this->fail($exception->getMessage());
        }

        return $dbservice;
    }

    /**
     * @depends testConnection
     */
    public function testSelectDB($dbservice) {
        try {
            $dbservice->selectDB(Config::$database->usageDatabase);
        } catch (RuntimeException $exception) {
            $this->fail($exception->getMessage());
        }

        return $dbservice;
    }

    /**
     * @depends testConnection
     */
    public function testGetDatabase($dbservice) {
        $db = $dbservice->getDatabase();
        $this->assertNotNull($db);
        $this->assertInstanceOf('mysqli',$db);
        return $db;
    }
}
