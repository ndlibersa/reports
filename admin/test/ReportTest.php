<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ReportTest extends PHPUnit_Framework_TestCase {

    protected $param_values;

    protected function setUp() {
        $this->param_values = array();
    }

    /**
     * @dataProvider reportIdProvider
     */
    public function testReportId($id) {
        $report = ReportFactory::makeReport($id);
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
    public function testReportIdParams($id) {
        $report = ReportFactory::makeReport($id);
        Parameter::setReport($report);

        $params = $report->getParameters();
        foreach ($params as $parm ) {
            $this->initParamValues($parm);
        }


        $report->run(false,true);
        $report->run(true,true);
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
        $this->param_values = array($parm->id => $parm->value);

        if ($parm instanceof CheckboxParameter || is_subclass_of($parm,'CheckboxParameter') ) {
            $this->param_values[$parm->id] = array('Y');
        } else if ($parm instanceof DateRangeParameter) {
        } else if ($parm instanceof DropdownParameter || is_subclass_of ($parm, 'DropdownParameter')) {
            foreach ($parm->getSelectValues($parm->parentID) as $v) {
                $this->param_values[$parm->id][] = $v['cde'];
            }
        }
    }

    public function testReportHeaders() {
        
    }
}