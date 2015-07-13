<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsageStatisticsYearlyUsageStatistics
 *
 * @author bgarcia
 */
class UsageStatisticsYearlyUsageStatistics extends Report {
    public function __construct($id) {
        parent::__construct($id);
    }

    public function orderBy() {
        return "order by TITLE_SORT";
    }

    public function sql($isArchive) {
        $yearFields = '';
        for ($y = 2015; $y >= 2008; $y--) {
            $yearFields .= "max(IF(year=$y, totalCount, null)) '{$y}_ytd',";
        }

        return "
SELECT t.Title TITLE,
PRINT_ISSN,
ONLINE_ISSN,
pp.reportDisplayName PUBLISHER,
Platform.reportDisplayName PLATFORM,
t.resourceType RESOURCE_TYPE,
$yearFields
sum(totalCount) 'all_years',
t.titleID, pp.platformID,
replace(replace(replace(t.Title,'A ',''),'An ',''),'The ','') TITLE_SORT
FROM Title t, (SELECT titleID,
  MAX(IF(identifierType='ISSN', concat(substr(identifier,1,4), '-', substr(identifier,5,4)),null)) PRINT_ISSN,
  MAX(IF(identifierType='online', concat(substr(identifier,1,4), '-', substr(identifier,5,4)),null)) ONLINE_ISSN
  FROM TitleIdentifier GROUP BY titleID) ti,
Platform, Publisher, PublisherPlatform pp, YearlyUsageSummary yus
WHERE t.titleID = yus.titleID
AND pp.publisherPlatformID = yus.publisherPlatformID
AND pp.platformID = Platform.platformID
AND pp.publisherID = Publisher.publisherID
AND ti.titleID=t.titleID
{$this->addWhere[0]} AND yus.archiveInd=". intval($isArchive)
. " GROUP BY t.Title, print_issn, online_issn, pp.reportDisplayName, Platform.reportDisplayName, t.titleID, pp.platformID";
    }
}
