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


if($do == 'photo')
{
    $sid = 7;
    $command    = 'FF_PHOTO';
    $parameters = ' ';
    $order = $command.' '.$parameters;
    $valueArray  = array($sid,$order,'new');
    $columnArray = array(SXN_CONTROL_COMMANDS_COLUMN_SID,
                        SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
                        SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    $g_dbM3->insertRow(SXN_CONTROL_TABLE_COMMANDS,$columnArray,$valueArray);
    $sid = 8;
    $command    = 'FF_PHOTO';
    $parameters = ' ';
    $order = $command.' '.$parameters;
    $valueArray  = array($sid,$order,'new');
    $columnArray = array(SXN_CONTROL_COMMANDS_COLUMN_SID,
                        SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
                        SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    $g_dbM3->insertRow(SXN_CONTROL_TABLE_COMMANDS,$columnArray,$valueArray);
}
$sid302 = lib_getLatestValue(1);
echo("<body>");
echo("<h1><a href=\"sxn_sxn.php\">Control Saxen Heater</h1><br>");
echo("<h2><a href=\"sxn_sxn.php?do=photo\">++ </a></h2>");
echo("<h2><a href=\"sxn_sxn.php\"> Uppdatera</a></h2>");
echo("<h1>Elektricitet $sid302 Watt</h1> <br>");
echo("</body></html>");
?>
