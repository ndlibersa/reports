<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportFactory
 *
 * @author bgarcia
 */
class ReportFactory {
    public static function makeReport($reportID) {
        switch ($reportID) {
            case 1: return new UsageStatisticsByTitles(1);
            case 2: return new UsageStatisticsByProviderPublisher(2);
            case 3: return new UsageStatisticsProviderRollup(3);
            case 4: return new UsageStatisticsPublisherRollup(4);
            case 5: return new UsageStatisticsTopJournalRequests(5);
            case 6: return new UsageStatisticsYearlyUsageStatistics(6);
            default: throw new \BadFunctionCallException("Did not recognize reportID");
        }
    }
}
