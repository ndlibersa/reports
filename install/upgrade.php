<?php

include_once 'bootstrap.php';
Config::init();

if (isset($_POST['step'])) {
    $step = $_POST['step'];
} else {
    $step = 0;
}

$errorMessage = array();
if ($step == "1"){
    $dbservice = new DBService();

    //make sure SQL file exists
    $test_sql_file = "test_create.sql";
    $sql_file = "create_tables_data.sql";

    if (!file_exists($test_sql_file)) {
        $errorMessage[] = "Could not open sql file: " . $test_sql_file . ".  If this file does not exist you must download new install files.";
    }else{
        //run the file - checking for errors at each SQL execution
        $f = fopen($test_sql_file,"r");
        $sqlFile = fread($f,filesize($test_sql_file));
        $sqlArray = explode(";",$sqlFile);

        //Process the sql file by statements
        foreach ($sqlArray as $stmt) {
           if (strlen(trim($stmt))>3){
                //replace the DATABASE_NAME parameter with what was actually input
                $stmt = str_replace("_DATABASE_NAME_", Config::$database->name, $stmt);

                try {
                    $result = $dbservice->query($stmt);
                } catch (RuntimeException $exception) {
                    $errorMessage[] = $dbservice->error() . "<br /><br />For statement: " . $stmt;
                    break;
                }
            }
        }

    }

    //once this check has passed we can run the entire ddl/dml script
    if (count($errorMessage) == 0){
        if (!file_exists($sql_file)) {
            $errorMessage[] = "Could not open sql file: " . $sql_file . ".  If this file does not exist you must download new install files.";
        }else{
            //run the file - checking for errors at each SQL execution
            $f = fopen($sql_file,"r");
            $sqlFile = fread($f,filesize($sql_file));
            $sqlArray = explode(';',$sqlFile);



            //Process the sql file by statements
            foreach ($sqlArray as $stmt) {
               if (strlen(trim($stmt))>3){
                    //replace the DATABASE_NAME parameter with what was actually input
                    $stmt = str_replace("_DATABASE_NAME_", Config::$database->name, $stmt);

                    try {
                        $result = $dbservice->query($stmt);
                    } catch (RuntimeException $exception) {
                        $errorMessage[] = $dbservice->error() . "<br /><br />For statement: " . $stmt;
                        break;
                    }
                }
            }

        }
    }

	if (count($errorMessage) == 0) {
        //passed db host, name check, test that user can select from Reports database
        try {
            $result = $dbservice->query("SELECT reportID FROM " . Config::$database->name . ".Report WHERE reportName like '%Usage%';");
        } catch (RuntimeException $exception) {
            $errorMessage[] = "Unable to select from the Report table in database '" . Config::$database->name . "' with user '" . Config::$database->username . "'.  Error: " . mysql_error();
        }

        if (count($errorMessage)===0) {
            while ($row = $result->fetchRowPersist()) {
                $reportID = $row[0];
            }
        }
    }

    if (count($errorMessage)) {
        $step=0;
    }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CORAL Reports Database Upgrade</title>
<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
<center>
<table style='width:700px;'>
<tr>
<td style='vertical-align:top;'>
<div style="text-align:left;">


<?php if(!$step){ ?>
	<h3>Welcome to a new CORAL Usage Reporting Database Upgrade!</h3>
	This upgrade will:
	<ul>
		<li>Perform safety checks similar to the installation script</li>
		<li>Connect to MySQL and recreate the CORAL Usage Reporting tables</li>
	</ul>

	<br />
	To get started you should have:
	<ul>
		<li>Previously run the installation script.</li>
	</ul>


	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type='hidden' name='step' value='1'>
	<input type="submit" value="Continue" name="submit">
	</form> <?php
} else if ($step == '1'){
	echo "<h3>CORAL Usage Reporting Database Upgrade is now complete!</h3>";
}
?>

</td>
</tr>
</table>
<br />
</center>


</body>
</html>