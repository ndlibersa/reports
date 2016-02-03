<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportInterface
 *
 * @author bgarcia
 */
interface ReportInterface {

    public function sql($isArchive);

    public function applyDateRange(array $dateRange);

    public function run($isArchive, $allowSort);

    // returns outlier array for display at the bottom of reports
    public function getOutliers();

    // returns associated parameters
    public function getParameters();

    // removes associated parameters
    public function getColumnData();

    // return the title of the ejournal for this report
    public function getUsageTitle($titleID);

    public function getLinkResolverLink(&$row);
}
