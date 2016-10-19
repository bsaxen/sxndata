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

echo("</head> ");
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$labelTargetTemperature   = "CSH_targetTemperature";
	
$do = (isset($_GET['do']) ? $_GET['do'] : null);


if($do == 'csl')
{
	system("rm -f log/CSH.log");
}

if($do == 'inc')
{
    $targetTemp = (int)lib_recall($labelTargetTemperature) + 1.0; 	
    lib_remember($labelTargetTemperature,$targetTemp);
}
if($do == 'dec')
{
    $targetTemp = (int)lib_recall($labelTargetTemperature) - 1.0; 	
    lib_remember($labelTargetTemperature,$targetTemp);
}
$targetTemp = (int)lib_recall($labelTargetTemperature); 
$burnerStatus = (int)lib_recall("CSH_smokeDir"); 
if($burnerStatus == 1) $burner_ON_OFF = "ON";
if($burnerStatus == 2) $burner_ON_OFF = "OFF";
$waterTempOut = lib_getLatestValue(1);
$waterTempIn  = lib_getLatestValue(3);
$outdoorTemp  = lib_getLatestValue(9);
$indoorTemp   = lib_getLatestValue(2);
$smokeTemp    = lib_getLatestValue(4);
	
$energy = 100*($waterTempOut - $waterTempIn);
echo("<body>");
echo("<table border=\"1\">");
echo("<tr><td><a href=\"sxn_csh.php\">Control Saxen Heater</a></td></tr>");
echo("<tr><td><a href=\"sxn_csh.php?do=inc\">Target + </a></td></tr>");
echo("<tr><td><a href=\"sxn_csh.php?do=dec\">Target - </a></td></tr>");
echo("<tr><td><a href=\"sxn_csh.php?do=log\"> View Log</a></td></tr>");
echo("<tr><td><a href=\"sxn_csh.php?do=csl\"> Clear Log</a></td></tr>");
echo("<tr><td><a href=\"sxn_csh.php\"> Uppdatera</a></td></tr>");
echo("</table>");	
echo("<table border=\"1\">");
if($burner_ON_OFF=="ON")(echo("<tr><td>Burner Status</td><td bgcolor=\"#37a30e\">$burner_ON_OFF</td></tr>");
if($burner_ON_OFF=="OFF")(echo("<tr><td>Burner Status</td><td bgcolor=\"#c13710\">$burner_ON_OFF</td></tr>");
echo("<tr><td>Energy Consumption</td><td>$energy</td></tr>");
echo("<tr><td>Target Temperature</td><td>$targetTemp</td></tr>");
if($waterTempOut < 25.0)
	echo("<tr><td>Water Out</td><td bgcolor=\"#c13710\">$waterTempOut</td></tr>");
else
	echo("<tr><td>Water Out</td><td bgcolor=\"##37a30e\">$waterTempOut</td></tr>");  
echo("<tr><td>Water In</td><td>$waterTempIn</td></tr>");
echo("<tr><td>Outdoor</td><td>$outdoorTemp</td></tr>");
echo("<tr><td>Indoor</td><td>$indoorTemp</td></tr>");
echo("<tr><td>Smoke</td><td>$smokeTemp</td></tr>");
echo("</table>");
if($do == 'log')
{
    $ii = 0;
    $handle = fopen("log/CSH.log", "r");
    if ($handle) 
    { 
	echo("<table border=\"1\">");
        while (($line = fgets($handle)) !== false) 
        {
		$ii++;
		if($line == "C_UP)
			echo("<p><tr><td bgcolor=\"#c13710\" >$ii $line</td></tr></p>");
		else if($line == "C_DOWN")
			echo("<p><tr><td bgcolor=\"#37a30e\" >$ii $line</td></tr></p>");
		else
		  	echo("<p><tr><td>$ii $line</td></tr></p>");	
	}
	echo("</table>");
    }
}	
echo("</body></html>");
?>
