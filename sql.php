<?php
require_once('sxn_sql_lib.php');
require_once('sxn_definition.php');
    //$sid = $_GET['sid'];
    //parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $sid = (isset($_GET['sid']) ? $_GET['sid'] : null);
    //$sid = $_GET['sid'];
    if(!$sid)$sid = 901;
    //echo("**** sid=$sid ****"); die;
    $username = "root"; 
    $password = "amazon";   
    $host = "localhost";



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


    $database="nb_db_collector";
    $server = mysql_connect($host, $username, $password);
    $connection = mysql_select_db($database, $server);
    $myquery = "SELECT id, ts, nb_value FROM  nb_data_$sid WHERE id < 10000";
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