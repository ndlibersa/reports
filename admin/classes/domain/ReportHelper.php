<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ReportHelper{
	public $report;
	public $ID;
	public $outputType;
	public $startPage;
	public $sortColumn = null;
	public $sortOrder = null;
	public $titleID = null;

	public $addWhere = array('','');

	public $hidden_inputs;
	public $paramDisplay;
	public $rprt_prm_add;

	public $showUnadjusted = false;
	public $maxRows;

	public $outlier;
	public $sumColsArray = array();
	public $groupColsArray = array();

	public $baseURL = null;
	public $perform_subtotal_flag;

	public function __construct(){
		if(isset($_REQUEST['reportID']))
			$this->ID = $_REQUEST['reportID'];
		else
			$this->ID = header('Location: index.php');

		if(isset($_REQUEST['outputType']))
			$this->outputType = $_REQUEST['outputType'];
		else
			$this->outputType = 'web';

		if ($this->outputType != 'web'){
			$this->startPage=1;
		} else if (isset($_REQUEST['startPage'])) {
			$this->startPage = $_REQUEST['startPage'];
		} else {
			$this->startPage = 1;
		}

		if(isset($_REQUEST['sortColumn']))
			$this->sortColumn = $_REQUEST['sortColumn'];
		if(isset($_REQUEST['sortOrder']))
			$this->sortOrder = $_REQUEST['sortOrder'];
		if(isset($_REQUEST['titleID']))
			$this->titleID = $_REQUEST['titleID'];
		$this->report = new Report($this->ID);

		$this->hidden_inputs = new HiddenInputs();

		$this->loopThroughParams();

		$this->sumColsArray = $this->report->getReportSums();
		$this->groupColsArray = $this->report->getGroupingColumns();
		if(count($this->groupColsArray)>0)
			$this->perform_subtotal_flag = true;
		else
			$this->perform_subtotal_flag = false;

		$this->outlier = $this->report->getOutliers();

		Config::init();
		if(Config::$settings->baseURL){
			$hasQ = strpos(Config::$settings->baseURL,'?')>0;
			$this->baseURL = Config::$settings->baseURL . ($hasQ ? '&' : '?');
		}
	}

	public function process(ReportResult &$reportArray, ReportNotes &$notes){
		$countForGrouping = 0;
		$sumArray = array();
		$totalSumArray = array();
		$holdArray = array();

		if ($reportArray->numFields !== 0) {
			echo '<thead>';
			$this->processColumns($reportArray->fields);
			echo '</thead>';
			
			//loop through resultset
			$groupArrayCount = count($this->groupColsArray);
			$rowNum = 1;
			echo '<tbody>';
			while ($currentRow = $reportArray->data->fetch_assoc()) {

				if (isset($currentRow['platformID']))
					$notes->platIDs[$currentRow['platformID']] = '';
				if (isset($currentRow['publisherPlatformID']))
					$notes->pubIDs[$currentRow['publisherPlatformID']] = '';

				$reset = ($rowNum === $this->startPage);

				$performCheck = false;
				$print_subtotal_flag = false;

				$rowoutput = "<tr>";

				$colNum = 0;

				foreach ($currentRow as $field => $data) {
					//stop displaying columns once we hit title ID or platform ID
					if (($field === 'titleID') || ($field === 'platformID')) {
						break;
					}

					if ($data === '')
						$data = '&nbsp;';

					//if sort is explicitly requested we will group on this column if it is allowed according to DB
					if (isset($holdArray[$colNum])
						&& isset($this->groupColsArray[$field])
						&& ($data != $holdArray[$colNum])) {

						if ($this->sortColumn === $colNum + 1) {
							$hold_rprt_grpng_data = $holdArray[$colNum];
							$print_subtotal_flag = true;
							//if no sort order is specified, use default grouping
						} else if (($this->sortOrder === '') && ($holdArray[$colNum] != '')) {
							$this->groupColsArray[$field] = true;
							//default echo flag to Y, we will reset later
							$print_subtotal_flag = true;
							$performCheck = true;
							if($groupArrayCount === 1)
								$hold_rprt_grpng_data = $holdArray[$colNum];
							else
								$hold_rprt_grpng_data = 'Group';
						}
					}
					if ($data != (isset($holdArray[$colNum]) ? $holdArray[$colNum] : null)
						|| $reset
						|| $this->outputType === 'xls'
						|| ($this->perform_subtotal_flag
							&& $this->sortOrder === ''
							&& $groupArrayCount > 1)) {

						$reset = true;
						$print_data = $data;
					}
					//echo it out if it's a number, needs to be printed regardless
					else if ($print_data == '0'.$print_data)
						$print_data = '&nbsp;';
					else
						$print_data = $data;

					if ($this->outputType === 'web' && ($print_data !== '&nbsp;') && $field === 'TITLE') {
						if ($this->ID != '1') {
							$print_data .= '<br><font size="-4"><a target="_BLANK" href="report.php?reportID=1&prm_4=' . ($this->showUnadjusted ?'Y':'N') . '&titleID='
								. $currentRow['titleID']
								. '&outputType=web">view related titles</a></font>';
						}

						//echo link resolver link
						if ((($currentRow['PRINT_ISSN']) || ($currentRow['ONLINE_ISSN'])) && isset($this->baseURL)) {
							$print_data .= '<br><font size="-4"><a target="_BLANK" href="'
								. $this->getLinkResolverLink($currentRow)
								. '">view in link resolver</a></font>';
						}
					}

					if(isset($currentRow[$field . '_OVERRIDE']) || (isset($currentRow[$field . '_OUTLIER']) && $currentRow[$field . '_OUTLIER'] > 0)) {
						if (!$this->showUnadjusted && isset($currentRow[$field . '_OVERRIDE'])) {
							$rowoutput .= "<td class='overriden'>" . $currentRow[$field . '_OVERRIDE'] . "</td>";
						} else {
							if ($this->showUnadjusted) {
								if ($currentRow[$field . '_OUTLIER'] >= 4) {
									$tmp_outlier_color = Color::$levelColors[$currentRow[$field . '_OUTLIER'] - 3];
								} else {
									$tmp_outlier_color = Color::$levelColors[$currentRow[$field . '_OUTLIER']];
								}
								$rowoutput .= "<td class='" . $tmp_outlier_color[1] . "'>" . $print_data . "</td>";
								unset($tmp_outlier_color);
							} else {
								$rowoutput .= "<td class='flagged'>" . $print_data . "</td>";
							}
						}
					} else if (isset($currentRow['MERGE_IND'])) {
						$rowoutput .= "<td class='merged'>" . $print_data . "</td>";
					} else {
						$rowoutput .= "<td>" . $print_data . "</td>";
					}

					$holdArray[$colNum] = $data;

					if (isset($this->groupColsArray[$field])) {
						if ($this->sortColumn === $colNum + 1) {
							$hold_rprt_grpng_data = $holdArray[$colNum];
						} else if ($this->sortOrder === '') {
							if($groupArrayCount === 1)
								$hold_rprt_grpng_data = $holdArray[$colNum];
							else
								$hold_rprt_grpng_data = 'Group';
						}
					}
					// get the numbers out for summing
					if (isset($sumArray[$field])) {
						$sumArray[$field][] = $data;
						$totalSumArray[$field] += $data;//filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) );
					} else {
						$sumArray[$field] = array($data);
						$totalSumArray[$field] = $data;//filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) );
					}
					//end if display columns is Y
					++$colNum;
				}//end loop through columns
				++$countForGrouping;

				// loop through the group arrays, if any are N then echo flag is N otherwise it will be left to Y
				//determine if the current row needs to be grouped
				if ($this->outputType != 'xls'
					&& !($performCheck && in_array(false, $this->groupColsArray, true) !== false)
					&& $print_subtotal_flag
					&& $this->report->groupTotalInd === '1')
				{
					$rowoutput .= "</tr>";

					if ($countForGrouping > 1) {
						$rowoutput .= '<tr class="data"><td class="sum">Total for '
							. $hold_rprt_grpng_data . '</td>';
						foreach($reportArray->fields as $field) {
							$rowoutput .= '<td class="sum">'
								. $this->sumField($this->sumColsArray[$field], $sumArray[$field])
								. '</td>';
						}
						$rowoutput .= '</tr>';
					}
					$sumArray = array();
					$countForGrouping = 0;
					$rowoutput .= '<tr class="data"><td colspan=' . $reportArray->numFields . '>&nbsp;</td></tr>' . "<tr class='data'>";
				}

				++$rowNum;

				if($this->maxRows <= 0)
					echo $rowoutput;
				else if ($rowNum < $this->maxRows)
					echo $rowoutput;

				if($rowNum%15000 === 0)
					ob_flush();
			}
			--$rowNum;

			if ($this->outputType != 'xls' && $this->perform_subtotal_flag) {
				if ($this->report->groupTotalInd === '1' && $hold_rprt_grpng_data) {
					//  one last grouping summary
					if ($countForGrouping > 1) {
						$grp .= '<tr class="data"><td class="sum">Total for '
							. $hold_rprt_grpng_data . '</td>';
						foreach($reportArray->fields as $field) {
							$grp .= '<td class="sum">'
								. $this->sumField($this->sumColsArray[$field], $sumArray[$field])
								. '</td>';
						}
						echo $grp . '</tr>';
					}
					echo "<tr class='data'><td colspan=" . $reportArray->numFields . ">&nbsp;</td></tr>";
				}
				echo '<tr class="data"><td class="sum">Total for Report</td>';
				$total = '';
				for ($colNum=1; $colNum < $reportArray->numFields; ++$colNum) {
					if(isset($this->sumColsArray[$reportArray->fields[$colNum]])){
						$total = $this->sumColTotal($this->sumColsArray[$reportArray->fields[$colNum]],$totalSumArray[$reportArray->fields[$colNum]],$rowNum);
					}
					if($total)
						echo '<td class="sum">' . $total . '</td>';
					else
						echo '<td class="sum">&nbsp;</td>';
				}
				echo '</tr>';
			}

			if ($rowNum === 0){
				echo '<tr class="data"><td colspan="' . $reportArray->numFields . '"><i>Sorry, no rows were returned.</i></td></tr>';
			}else{
				echo "<tr><td colspan='$reportArray->numFields' align='right'><i>Showing rows {$this->startPage} to ";
				if (($this->maxRows>0)&&($rowNum>$this->maxRows)){
					echo "$this->maxRows of $this->maxRows</i></td></tr>";
				} else {
					echo "$rowNum of $rowNum</i></td></tr>";
				}
			}
			
			echo '</tbody>';
		}
		
	}

	public function processColumns(&$currentRow) {
		$colcount = 1;
		foreach ($currentRow as $field) {
			echo "<th>" . ucwords(strtolower(strtr($field, '_', ' ')));
			if ($this->outputType === 'web') {
?>
<div style='width:100%;min-width:22px;align:center;margin:0px;padding:0px;'>
<a href="javascript:sortRecords('<?php echo $colcount; ?>', 'asc');"  style="border:none">
<img align='center' src='images/arrowdown<?php

				if ($this->sortColumn == $colcount && $this->sortOrder === 'asc')
					echo '_sel';

?>.gif' border=0></a>&nbsp;
<a href="javascript:sortRecords('<?php echo $colcount; ?>', 'desc');"  style="border:none">
<img align='center' src='images/arrowup<?php

				if ($this->sortColumn == $colcount && $this->sortOrder === 'desc')
					echo '_sel';

?>.gif' border=0></a>
</div>
<?php
			}
			echo "</th>";
			++$colcount;
		}
	}

	private function getParamValue(ReportParameter &$parm){
		if ($parm->parameterTypeCode === 'ms' && (!isset($_REQUEST['useHidden']) || $_REQUEST['useHidden'] == null)) {
			return implode("', '",
				explode(',',
					str_replace('\\\\', ',',
						$_REQUEST['prm_' . $parm->reportParameterID])
				)
			);
		} else if(isset($_REQUEST['prm_' . $parm->reportParameterID])) {
			return trim($_REQUEST['prm_' . $parm->reportParameterID]);
		}
		return '';
	}

	private function loopThroughParams(){
		if($this->titleID === null){
			$this->hidden_inputs->addReportID($this->ID);
			$this->paramDisplay = '';
			$this->rprt_prm_add = '';
		}else {
			$this->paramDisplay = '<b>Title:</b> ' . $this->report->getUsageTitle($this->titleID) . '<br>';
			$this->rprt_prm_add = '&titleID=' . $this->titleID;

			$this->hidden_inputs->addReportID($this->ID)
								->addTitleID($this->titleID);
		}

		foreach ($this->report->getParameters() as $parm) {
			$prm_value = $this->getParamValue($parm);
			$this->rprt_prm_add .= '&prm_' . $parm->reportParameterID . '=' . $prm_value;
			if ($prm_value) {
				if ($parm->parameterTypeCode === 'chk') {
					if (($prm_value === 'on') || ($prm_value === 'Y')) {
						$this->showUnadjusted = true;
						$this->hidden_inputs->addParam($parm->reportParameterID,'Y');
						$this->paramDisplay .= '<b>Numbers are not adjusted for use violations</b><br>';
					}
				} else if ($parm->parameterAddWhereClause === 'limit') {
					//decide what to do
					$this->addWhere[0] = ''; //changed from $add_where. Assumed mistake.
					$this->maxRows = $prm_value;
					$this->paramDisplay .= '<b>Limit:</b> Top '
						. $prm_value
						. '<br>';
				} else {
					//if the parm comes through as an id (for publisher / platform or title), display actual value for user friendliness
					if (($parm->parameterDisplayPrompt === 'Provider / Publisher') || ($parm->parameterDisplayPrompt === 'Provider') || ($parm->parameterDisplayPrompt === 'Publisher')) {
						$this->paramDisplay .= '<b>'
							. $parm->parameterDisplayPrompt
							. ':</b> '
							. implode(', ', $parm->getPubPlatDisplayName($prm_value))
							. '<br>';
					} else {
						// only display the param info at the top if it was entered
						$this->paramDisplay .= '<b>'
							. $parm->parameterDisplayPrompt
							. ":</b> '"
							. $prm_value
							. "'<br>";
					}
					$prm_value = strtoupper($prm_value);
					//now make the actual change to the SQL statement parameter
					/*if ($parm->parameterAddWhereNumber === 2) {
						$this->addWhere[1] = str_replace('PARM', $prm_value,
							$this->addWhere[1] . ' AND ' . $parm->parameterAddWhereClause);
					} else {
						$this->addWhere[0] = str_replace('PARM', $prm_value,
							$this->addWhere[0] . ' AND ' . $parm->parameterAddWhereClause);
					}*/
					if ($parm->parameterAddWhereNumber == 2){
						$addWhereNum = 1;
					}else{
						$addWhereNum = 0;
					}

					$prm_value=strtoupper($prm_value);
					$this->addWhere[$addWhereNum] =
						preg_replace('/PARM/', $prm_value,
							$this->addWhere[$addWhereNum]
							. ' AND ' . $parm->parameterAddWhereClause);

					$this->hidden_inputs->addParam($parm->reportParameterID,$prm_value);
				}
			}
		}

		//if titleID was passed in, add that to addwhere
		if (($this->ID === '1') && ($this->titleID != '')){
			$this->addWhere[1] .= ' AND t.titleID = ' . $this->titleID;
		}
	}

	public function getReportResults($isArchive){
		if (stripos($this->report->reportSQL, 'mus'))
			$ch = 'm';
		else
			$ch = 'y';

		if($isArchive){
			$archiveInd = 1;
			$orderBy = '';
		} else{
			$archiveInd = 0;
			if($this->sortColumn)
				$orderBy = ' ORDER BY ' . $this->sortColumn . ' ' . $this->sortOrder;
			else
				$orderBy = ' ' . $this->report->orderBySQL;
		}
		return new ReportResult($this->report->reportDatabaseName,
			str_replace('ADD_WHERE', $this->addWhere[0] . ' AND ' . $ch . 'us.archiveInd = ' . $archiveInd,
				str_replace('ADD_WHERE2', $this->addWhere[1], $this->report->reportSQL))
				. $orderBy);
	}

	public function getLinkResolverLink(&$thisRow){
		if($thisRow['PRINT_ISSN']){
			if (($thisRow['ONLINE_ISSN'])){
				return $this->baseURL . 'rft.issn=' . $thisRow['PRINT_ISSN'] . '&rft.eissn=' . $thisRow['ONLINE_ISSN'];
			}else{
				return $this->baseURL . 'rft.issn=' . $thisRow['PRINT_ISSN'];
			}
		}else{
			return $this->baseURL . 'rft.eissn=' . $thisRow['ONLINE_ISSN'];
		}
	}

	public function sumField($sumType, array &$sumArray){
		$total = '';
		if($sumType === 'dollarsum'){
			$total = array_sum($sumArray);
			if($total > 0)
				return money_format($total);
		} else if($sumType === 'sum'){
			foreach ($sumArray as $amt){
				if ($amt >= '0'){
					$total += $amt;
				}
			}
			if($total >= '0')
				return number_format($total);
		}
		return '&nbsp;';
	}

	private function sumColTotal($sumType,$totalSum,$rowcount){
		if($sumType==='dollarsum') {
			return money_format($totalSum);
		} else if($sumType==='sum') {
			$total=number_format($totalSum);
			if (!$total)
				$total = '-';
			return $total;
		} else if($sumType==='avg') {
			return ($rowcount > 0 )
				? (number_format($totalSum / $rowcount) . '%')
				: '';
		}
		return '';
	}
}
