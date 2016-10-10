<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="Your description goes here" />
	<meta name="keywords" content="your,keywords,goes,here" />
	<meta name="author" content="Your Name" />
	<link rel="stylesheet" type="text/css" href="sxndata.css" title="Variant Duo" media="screen,projection" />
	<title>SXN Data Admin</title>
<?php
//==================================================
// Control Saxen Heater
// sxn_csh.php
// 2016-10-10
//==================================================
require_once('sxn_sql_lib.php');
//require_once('sxn_definition.php');
require_once('sxn_lib.php');
ini_set('display_errors',1);
error_reporting(E_ALL);
//echo("START ");
echo("</head> ");
$docRoot = $_SERVER['DOCUMENT_ROOT'];
//echo("<br>$docRoot<br>");

$do = (isset($_GET['do']) ? $_GET['do'] : null);


if($do == 'inc')
{
    $sid = 1;
    $command    = 'NBC_STEPPER_CTRL 1 10 20';
    $parameters = ' ';
    $order = $command.' '.$parameters;
    $valueArray  = array($sid,$order,'new');
    $columnArray = array(SXN_CONTROL_COMMANDS_COLUMN_SID,
                        SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
                        SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    $g_dbM3->insertRow(SXN_CONTROL_TABLE_COMMANDS,$columnArray,$valueArray);
}
$waterTempOut = lib_getLatestValue(1);
$waterTempIn  = lib_getLatestValue(3);
echo("<body>");
echo("<h1><a href=\"sxn_csh.php\">Control Saxen Heater</h1><br>");
echo("<h2><a href=\"sxn_csh.php?do=inc\">++ </a></h2>");
echo("<h2><a href=\"sxn_csh.php?do=dec\">-- </a></h2>");
echo("<h2><a href=\"sxn_csh.php\"> Uppdatera</a></h2>");
echo("<h1>Water Out $waterTempOut</h1> <br>");
echo("<h1>Water In $waterTempIn</h1> <br>");
echo("</body></html>");
?>
