<?php

class ReportTable {
    public static $maxRows;
    public $columnData;

    public function __construct(Report $report, array $fields) {
        $i = 0;
        foreach ($fields as $fld) {
			$i++;
            if(in_array($fld,$report->ignoredCols)) {
                continue;
			}
            $_fields[$i] = $fld;
        }

        if ($report->flagManualSubtotal && $_fields[$i] === "outlier_flag") {
            $_fields[$i] = 'Total';
            $_fields[] = "outier_flag";
        }

        $this->columnData = $report->getColumnData();
        $this->columnData['name'] = $_fields;

        if ($report->flagManualSubtotal) {
            $this->columnData['sum']['Total'] = end($this->columnData['sum']);
        }
    }

    public function fields() {
        return $this->columnData['name'];
    }

    public function fieldAt($index) {
        return $this->columnData['name'][$index];
    }

    public function nfields() {
        return count($this->columnData['name']);
    }

    public static function filterRow(array $ignoredColumns, array $row) {
        $row_tmp = array();
        foreach ( $row as $field => $data ) {
            // stop displaying columns once we hit title ID or platform ID
            if (($field === 'titleID') || ($field === 'platformID')) {
                break;
            }
            if(in_array($field, $ignoredColumns)) {
                continue;
            }

            if ($field==='outlier_flag') {
                $row_tmp['Total'] = "&nbsp;";
            }

            $row_tmp[$field] = $data;
            if ($data === '') {
                $row_tmp[$field] = "&nbsp;";
            }
        }
        return $row_tmp;
    }

    public function getEmptyRow() {
        $row = array();
        foreach ($this->columnData['name'] as $v) {
            $row[$v] = null;
        }
        return $row;
    }

    public static function formatColumn(Report $report, $outputType, array $currentRow, $field, $value) {
        $colOutput = "";

        $value = str_replace(" & "," &amp; ",$value);

        if ($outputType === 'web'
            && ($value !== '&nbsp;') && $field === 'TITLE'
        ) {
            if ($report->id != '1') {
                $value .= "<br/><font size='-4'><a target='_BLANK' href=\"report.php?reportID=1&prm_4=" . ($report->showUnadjusted ? 'Y' : 'N');
                if (isset($currentRow['titleID'])) {
                    $value .= "&titleID={$currentRow['titleID']}&outputType=web\">view related titles</a></font>";
                } else {
                    $value .= "&outputType=web\">view related titles</a></font>";
                }
            }
            // echo link resolver link
            if ((($currentRow['PRINT_ISSN']) || ($currentRow['ONLINE_ISSN'])) && isset($report->baseURL)) {
                $value .= "<br/><font size=\"-4\"><a target=\"_BLANK\" href=\"" . $report->getLinkResolverLink($currentRow) . "\">view in link resolver</a></font>";
            }
        }
        if (isset($currentRow[$field . '_OVERRIDE']) || (isset($currentRow[$field . '_OUTLIER']) && $currentRow[$field . '_OUTLIER'] > 0)) {
            if (!$report->showUnadjusted && isset($currentRow[$field . '_OVERRIDE'])) {
                $colOutput .= "<td class='overriden'>" . $currentRow[$field . '_OVERRIDE'] . "</td>";
            } else {
                if ($report->showUnadjusted) {
                    if ($currentRow[$field . '_OUTLIER'] >= 4) {
                        $tmp_outlier_color = Color::$levels[$currentRow[$field . '_OUTLIER'] - 3];
                    } else {
                        $tmp_outlier_color = Color::$levels[$currentRow[$field . '_OUTLIER']];
                    }
                    $colOutput .= "<td class='$tmp_outlier_color[1]'>$value</td>";
                    unset($tmp_outlier_color);
                } else {
                    $colOutput .= "<td class='flagged'>$value</td>";
                }
            }
        } else if (isset($currentRow['MERGE_IND'])) {
            $colOutput .= "<td class='merged'>$value</td>";
        } else {
            $colOutput .= "<td>$value</td>";
        }
        return $colOutput;
    }

    public static function formatTotalsRow(array $row) {
        $str = null;
        $cspan = 0;
        $spanval = '';
        foreach($row as $name=>$val) {
            if ($str===null) {
                $cspan++;
                $spanval = $val;
                $str = "";
            } else if ($val==="&nbsp;"||$val==="") {
                $cspan++;
            } else {
                if($cspan>1) {
                    $str .= "<td class='sum' colspan='$cspan'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$spanval</td>";
                    $cspan = 0;
                    $spanval = '';
                }
                $str .= "<td class='sum'>$val</td>";
            }
        }
        if ($cspan) {
            $str .= "<td class='sum' colspan='$cspan'>$spanval</td>";
        }
        return "<tr class='data'>$str</tr>";
    }

    public function sumField($field, array &$sumArray) {
        if (!isset($sumArray[$field]))
            return '&nbsp;';

        $total = '';
        $sumType = $this->columnData['sum'][$field];
        if ($sumType === 'dollarsum') {
            $total = array_sum($sumArray[$field]);
            if ($total > 0)
                return money_format($total);
        } else if ($sumType === 'sum') {
            foreach ( $sumArray[$field] as $amt ) {
                if ($amt >= '0') {
                    $total += $amt;
                }
            }
            if ($total >= '0')
                return number_format($total);
        }
        return '&nbsp;';
    }

    public function sumColumn($field, $totalSum, $rowcount) {
        $sumType = $this->columnData['sum'][$field];
        if ($sumType === 'dollarsum') {
            return money_format($totalSum[$field]);
        } else if ($sumType === 'sum') {
            $total = number_format($totalSum[$field]);
            if (!$total)
                $total = '-';
            return $total;
        } else if ($sumType === 'avg') {
            return ($rowcount > 0) ? (number_format($totalSum[$field] / $rowcount) . '%') : '';
        }
        return '';
    }
}
