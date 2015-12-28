<?php
require_once('sxn_definition.php');
require_once('sxn_sql_lib.php');

    //$sid = $_GET['sid'];
    //parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $sid  = (isset($_GET['sid']) ? $_GET['sid'] : null);
    $op   = (isset($_GET['op']) ? $_GET['op'] : null);
    $sidX = (isset($_GET['sidX']) ? $_GET['sidX'] : null);
    $sidY = (isset($_GET['sidY']) ? $_GET['sidY'] : null);
    $date = (isset($_GET['startDate']) ? $_GET['startDate'] : null);
//echo("1 $startDate<br>");
    //$date = DateTime::createFromFormat('m/d/Y', $startDate);
    //$date = str_replace('/', '-', $startDate);
//echo("1 $date<br>");
    $tStart = date('Y-m-d', strtotime($date));
//echo("1 $tStart<br>");

    $date = (isset($_GET['endDate']) ? $_GET['endDate'] : null); 
//echo("2 $endDate<br>");
   // $date = str_replace('/', '-', $endDate);
//echo("2 $date<br>");
    $tEnd = date('Y-m-d', strtotime($date));
//echo("2 $tEnd<br>");
    //$tStart = '2015-12-01';
    //$tEnd = '2015-12-20';

    if(!$sid)$sid = 901;
    //echo("**** sid=$sid ****"); 
    $username = "root"; 
    $password = "amazon";   
    $host = "localhost";

    $table = SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid; 
    $tableX = SXN_COLLECTOR_TABLE_DATA_PREFIX.$sidX; 
    $tableY = SXN_COLLECTOR_TABLE_DATA_PREFIX.$sidY; 

//    $g_dbM2 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_COLLECTOR);
//
//    $g_dbM2->selectAllFromTable("nb_data_$sid", "");
//    $numRes = $g_dbM2->retrieveNumberOfResults();
//    echo("<br>Number of data: $numRes<br>");
//    while($data = $g_dbM2->retrieveResult())
//    {
//     $id          = $data[SXN_GENERAL_COLUMN_ID];
//     $ts          = $data[SXN_GENERAL_COLUMN_TIMESTAMP];
//     echo("$id $ts\n");
//           echo json_encode($data);
//    }
    //$data = $g_dbM2->retrieveResult();
    //echo json_encode($data);


    $database=SXN_DATABASE_COLLECTOR;
    $server = mysql_connect($host, $username, $password);
    $connection = mysql_select_db($database, $server);
if(!$op)
    $myquery = "SELECT * FROM  $table WHERE ts BETWEEN '$tStart 00:00:00' AND '$tEnd 23:59:59'";
//if($op == 'ADD')
//    $myquery = "SELECT $tableX. FROM  $table WHERE ts BETWEEN '$tStart 00:00:00' AND '$tEnd 23:59:59'";
//echo("$myquery");
    $query = mysql_query($myquery);
    if ( ! $query ) {
        echo mysql_error();
        die;
    }
    $data = array();
    for ($x = 0; $x < mysql_num_rows($query); $x++) {
        $data[] = mysql_fetch_assoc($query);
    }
    echo json_encode($data);
   
  //$g_dbM2->closeDataBase();
    mysql_close($server);
?>