<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsageStatisticsByProviderPublisher
 *
 * @author bgarcia
 */
class UsageStatisticsByProviderPublisher extends Report {

    public function __construct($id) {
        parent::__construct($id);

        $this->month_fields = "";
        $this->month_fields_override = "";
        $this->month_fields_outlier = "";
    }

    public function applyDateRange(array $dateRange) {
        $used = DateRangeParameter::getMonthsUsed($dateRange);
        $months = array('',
                'JAN','FEB','MAR','APR','MAY','JUN',
                'JUL','AUG','SEP','OCT','NOV','DEC'
                );

        $this->month_fields = "";
        $this->month_fields_override = "";
        $this->month_fields_outlier = "";
        for ($m = 1; $m<=12; ++$m) {
            if (isset($used[$m])) {
                $this->month_fields .= "MAX(IF(month=$m,usageCount,null)) `{$months[$m]}`,";
                $this->month_fields_override .= "MAX(IF(month=$m,overrideUsageCount,null)) {$months[$m]}_OVERRIDE,";
                $this->month_fields_outlier .= "MAX(IF(month=$m,outlierID,null)) {$months[$m]}_OUTLIER,";
            }
        }
    }

    public function sql($isArchive) {
        return "
SELECT t.Title TITLE, pp.reportDisplayName PUBLISHER,
Platform.reportDisplayName PLATFORM, t.resourceType RESOURCE_TYPE, mus.year YEAR,
MAX(IF(ti.identifierType='DOI', ti.identifier, null)) DOI,
MAX(IF(ti.identifierType='ISSN', concat(substr(ti.identifier,1,4), '-', substr(ti.identifier,5,4)),null)) PRINT_ISSN,
MAX(IF(ti.identifierType='eISSN', concat(substr(ti.identifier,1,4), '-', substr(ti.identifier,5,4)),null)) ONLINE_ISSN,
MAX(IF(ti.identifierType='ISBN', ti.identifier, null)) ISBN,
$this->month_fields
totalCount YTD_TOTAL,
ytdHTMLCount YTD_HTML,
ytdPDFCount YTD_PDF,
IF(MAX(outlierID)=0,'N','Y') OUTLIER_FLAG,
t.titleID titleID,
$this->month_fields_override
overrideTotalCount YTD_OVERRIDE,
overrideHTMLCount HTML_OVERRIDE,
overridePDFCount PDF_OVERRIDE,
$this->month_fields_outlier
yus.mergeInd mergeInd, pp.publisherPlatformID, pp.platformID,
replace(replace(replace(t.Title,'A ',''),'An ',''),'The ','') TITLE_SORT
FROM Platform, PublisherPlatform pp,
MonthlyUsageSummary mus LEFT JOIN YearlyUsageSummary yus ON yus.publisherPlatformID = mus.publisherPlatformID AND yus.year = mus.year AND yus.titleID = mus.titleID AND yus.archiveInd = mus.archiveInd,
Title t LEFT JOIN TitleIdentifier ti ON t.titleID = ti.titleID
WHERE Platform.platformID = pp.platformID

{$this->addWhere[0]} AND mus.archiveInd=". intval($isArchive)
. " AND mus.publisherPlatformID = pp.publisherPlatformID
AND mus.titleID = t.titleID
GROUP BY t.titleID, t.Title, pp.reportDisplayName, Platform.reportDisplayName, mus.year, overrideTotalCount, totalCount, overrideHTMLCount, ytdHTMLCount, overridePDFCount, ytdPDFCount, yus.mergeInd, pp.publisherPlatformID, pp.platformID";
    }
}