<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsageStatisticsTopJournalRequests
 *
 * @author bgarcia
 */
class UsageStatisticsTopJournalRequests extends Report {
    public function __construct($id) {
        parent::__construct($id);
    }

    public function sql($isArchive) {
        return "
SELECT t.Title TITLE,
max(pp.reportDisplayName) PUBLISHER,
GROUP_CONCAT(distinct Platform.reportDisplayName ORDER BY Platform.reportDisplayName DESC SEPARATOR ', ') PLATFORM,
t.resourceType RESOURCE_TYPE,
yus.year YEAR,
MAX(IF(ti.identifierType='DOI', ti.identifier, null)) DOI,
MAX(IF(ti.identifierType='ISSN', concat(substr(ti.identifier,1,4), '-', substr(ti.identifier,5,4)),null)) PRINT_ISSN,
MAX(IF(ti.identifierType='eISSN', concat(substr(ti.identifier,1,4), '-', substr(ti.identifier,5,4)),null)) ONLINE_ISSN,
MAX(IF(ti.identifierType='ISBN', ti.identifier, null)) ISBN,
sum(distinct totalCount) YTD_TOTAL,
sum(distinct ytdHTMLCount) YTD_HTML,
sum(distinct ytdPDFCount) YTD_PDF,
t.titleID titleID,
overrideTotalCount YTD_OVERRIDE,
overrideHTMLCount HTML_OVERRIDE,
overridePDFCount PDF_OVERRIDE,
replace(replace(replace(t.Title,'A ',''),'An ',''),'The ','') TITLE_SORT
FROM Title t, TitleIdentifier ti, Platform, Publisher, PublisherPlatform pp, YearlyUsageSummary yus
WHERE t.titleID = yus.titleID
AND pp.publisherPlatformID = yus.publisherPlatformID
AND pp.platformID = Platform.platformID
AND pp.publisherID = Publisher.publisherID
AND ti.titleID=t.titleID
{$this->addWhere[0]} AND yus.archiveInd=". intval($isArchive)
. " GROUP BY t.Title, yus.year, t.titleID";
    }
}
