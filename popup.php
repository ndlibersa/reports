<?php
/*
 * *************************************************************************************************************************
 * * CORAL Usage Statistics Reporting Module v. 1.0
 * *
 * * Copyright (c) 2010 University of Notre Dame
 * *
 * * This file is part of CORAL.
 * *
 * * CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * *
 * * CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * *
 * * You should have received a copy of the GNU General Public License along with CORAL. If not, see <http://www.gnu.org/licenses/>.
 * *
 * *************************************************************************************************************************
 */
include_once 'directory.php';

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="public">
	<title>CORAL Usage Statistics Reporting - <?php echo $pageTitle; ?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css"
		media="screen" />
	<link rel="stylesheet" href="css/thickbox.css" type="text/css"
		media="screen" />
	<link rel="stylesheet" href="css/datePicker.css" type="text/css"
		media="screen" />
	<link rel="stylesheet" href="css/jquery.autocomplete.css"
		type="text/css" media="screen" />
	<link rel="stylesheet" href="css/jquery.tooltip.css" type="text/css"
		media="screen" />
	<script type="text/javascript" src="js/plugins/jquery.js"></script>
	<script type="text/javascript" src="js/plugins/ajaxupload.3.5.js"></script>
	<script type="text/javascript" src="js/plugins/thickbox.js"></script>
	<script type="text/javascript" src="js/plugins/date.js"></script>
	<script type="text/javascript" src="js/plugins/jquery.datePicker.js"></script>
	<script type="text/javascript" src="js/plugins/jquery.autocomplete.js"></script>
	<script type="text/javascript" src="js/plugins/jquery.tooltip.js"></script>
	<script type="text/javascript" src="js/plugins/Gettext.js"></script>
	<script type="text/javascript" src="js/common.js"></script>

</head>
<body>
	
	
<?php
$type = $_GET['type'];

if ($type === 'report'){
	
	$report = new Report($_GET['value']);
	
	?>
<br />
	<center>
		<table width='400'>
			<tr>
				<td>
					<h2><?php echo $report->getName(); ?></h2>
<?php echo $report->getInfoText(); ?>
<br /> <br />
				</td>
			</tr>
		</table>
	</center>
<?php
}else{
	echo _('Invalid type!!');
}

?>


</body>
</html>
