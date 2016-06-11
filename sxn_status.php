<?php
//==================================================
// sxn_status.php
// 2016-06-11
//==================================================
session_start(); 

require_once('sxn_sql_lib.php');
require_once('sxn_lib.php');

$void    = $_SESSION['void'];

lib_listIpFiles(1,"ipList.work");


?>

