<?php
    //$sid = $_GET['sid'];
    //parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $sid = (isset($_GET['sid']) ? $_GET['sid'] : null);
    //$sid = $_GET['sid'];
    if(!$sid)$sid = 902;
    //echo("**** sid=$sid ****"); die;
    $username = "root"; 
    $password = "amazon";   
    $host = "localhost";
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
    mysql_close($server);
?>