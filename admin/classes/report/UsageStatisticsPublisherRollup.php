<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsageStatisticsPublisherRollup
 *
 * @author bgarcia
 */
class UsageStatisticsPublisherRollup extends Report {
    public function __construct($id) {
        parent::__construct($id);
    }

    public function applyDateRange(array $dateRange) {
        $used = DateRangeParameter::getMonthsUsed($dateRange);
        $months = array('',
                'JAN','FEB','MAR','APR','MAY','JUN',
                'JUL','AUG','SEP','OCT','NOV','DEC'
                );

        $this->month_fields = "";
        for ($m = 1; $m<=12; ++$m) {
            if (isset($used[$m]))
                $this->month_fields .= "sum(IF(month=$m,IFNULL(overrideUsageCount,usageCount),null)) `{$months[$m]}`,";
        }
    }

    public function sql($isArchive) {
        return "
SELECT pp.reportDisplayName Publisher,
Platform.reportDisplayName Platform,
number_of_titles,
mus.year,
$this->month_fields
total_count YTD_TOTAL,
html_count YTD_HTML,
pdf_count YTD_PDF,
Platform.platformID platformID,
pp.publisherPlatformID
FROM Platform, Publisher, PublisherPlatform pp, MonthlyUsageSummary mus,
        (SELECT pp.publisherPlatformID publisherPlatformID, count(distinct yus.titleID) number_of_titles, yus.year year,
        sum(totalCount) total_count, sum(ytdHTMLCount) html_count, sum(ytdPDFCount) pdf_count
        FROM Platform, Publisher, PublisherPlatform pp, YearlyUsageSummary yus
        WHERE pp.publisherPlatformID = yus.publisherPlatformID AND pp.platformID = Platform.platformID AND pp.publisherID = Publisher.publisherID
        GROUP BY pp.publisherPlatformID, yus.year) ytd
WHERE ytd.year = mus.year
AND pp.publisherPlatformID = ytd.publisherPlatformID
AND pp.publisherPlatformID = mus.publisherPlatformID
AND pp.platformID = Platform.platformID
AND pp.publisherID = Publisher.publisherID
{$this->addWhere[0]} AND mus.archiveInd=". intval($isArchive)
. " GROUP BY pp.reportDisplayName, Platform.reportDisplayName, mus.year, number_of_titles, total_count, html_count, pdf_count, pp.publisherPlatformID, Platform.platformID";
    }
}
