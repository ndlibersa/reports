<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ReportTest extends PHPUnit_Framework_TestCase {

    protected $paramValueList;

    protected function setUp() {
        $this->paramValueList = array();
    }

    /**
     * @dataProvider reportIdProvider
     * @depends testParams
     */
    public function testReportId($reportID) {
        $report = ReportFactory::makeReport($reportID);
        Parameter::setReport($report);
        foreach ($report->getParameters() as $parm ) {
            $parm->process();
        }
        $report->run(false,true);
        $report->run(true,true);
    }

    /**
     * @dataProvider reportIdProvider
     */
    public function testParams($reportID) {
        $this->setUp();

        $report = ReportFactory::makeReport($reportID);
        Parameter::setReport($report);

        $params = $report->getParameters();
        foreach ($params as $parm ) {
            $this->initParamValues($parm);
        }

        $imax = 0;
        foreach ($this->paramValueList as $paramVals) {
            $n = count($paramVals);
            if ($n>$imax) {
                $imax = $n;
            }
        }

        $i = 0;
        while ($i<$imax) {
            $params = $report->getParameters();
            foreach ($params as $parm ) {
                $this->initParamValues($parm);
            }
            foreach ($params as $p) {
                if (isset($this->paramValueList[$p->id])) {
                    $list = $this->paramValueList[$p->id];
                    $n = count($list);
                    if ($i<$n) {
                        $p->value = $this->paramValueList[$p->id][$i];
                    } else if ($n>0) {
                        $p->value = $this->paramValueList[$p->id][$n-1];
                    }
                }
            }
            ++$i;
        }
    }

    public function reportIdProvider() {
        $db = new DBService();
        $ids = array();
        foreach ( $db->query("SELECT reportID FROM Report")->fetchRows(MYSQLI_ASSOC) as $report ) {
            $ids[] = array($report['reportID']);
        }

        return $ids;
    }

    // only partial coverage
    public function initParamValues($parm) {
        $this->paramValueList = array($parm->id => $parm->value);

        if ($parm instanceof CheckboxParameter || is_subclass_of($parm,'CheckboxParameter') ) {
            $this->paramValueList[$parm->id] = array('Y');
        } else if ($parm instanceof DropdownParameter || is_subclass_of ($parm, 'DropdownParameter')) {
            if (trim($parm->sql)!=='') {
                throw new RuntimeException("parameter missing sql for test");
            }
            foreach ($parm->getSelectValues($parm->parentID) as $v) {
                $this->paramValueList[$parm->id][] = $v['cde'];
            }
        }
    }

    public function testReportHeaders() {

    }
}