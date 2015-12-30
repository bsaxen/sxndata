
<?php
session_start(); 

//require_once('sxn_definition.php');
require_once('sxn_sql_lib.php');
require_once('sxn_lib.php');



$startDate    = $_SESSION['startDate'];
$endDate      = $_SESSION['endDate'];

if(!startDate)
{
     $startDate = date("Y-m-d");
     $endDate   = date("Y-m-d");
}

$xZoom    = $_SESSION['xZoom'];
if(!$xZoom) $xZoom = 1;
$yZoom    = $_SESSION['yZoom'];
if(!$yZoom) $yZoom = 1;
$s_sid    = $_SESSION['s_sid'];

//$jsonX = SXN_GENERAL_COLUMN_ID;
//$jsonX = SXN_GENERAL_COLUMN_TIMESTAMP;
$jsonX = 'ts';
$jsonY = SXN_COLLECTOR_DATA_COLUMN_VALUE;

//$tsMin = (isset($_GET['tsmin']) ? $_GET['tsmin'] : null);
//$tsMax = (isset($_GET['tsmax']) ? $_GET['tsmax'] : null);


if(isset($_GET['date1']))
{
    $startDate = (isset($_GET['date1']) ? $_GET['date1'] : null);
    $startDate = date('Y-m-d', strtotime($startDate));
    //$startDate = $startDate.' 00:00:00';
}
if(isset($_GET['date2']))
{
    $endDate = (isset($_GET['date2']) ? $_GET['date2'] : null);
    $endDate = date('Y-m-d', strtotime($endDate));
    //$endDate = $endDate.' 23:59:59';
}

//$tStart = date('Y-m-d', strtotime($startDate));
//$tEnd   = date('Y-m-d', strtotime($endDate));

echo("<h1>SXNDATA 2015-12-30</h1>");
echo("Start Date: $startDate  End Date: $endDate<br>");
$do = (isset($_GET['do']) ? $_GET['do'] : null);

if($do=="select_sid")
{
 $s_sid = $_GET['sid'];   
}
if($do=="xzoom")
{
 $xZoom = $_GET['xZoom'];   
}
if($do=="yzoom")
{ 
 $yZoom = $_GET['yZoom'];   
}
if($do=="today")
{
     $startDate = date("Y-m-d");
     $endDate   = date("Y-m-d");
}


$yLabel = 'kWh';
// Set axis scale according to SID
if($s_sid)
{
    $stemp = SXN_COLLECTOR_TABLE_DATA_PREFIX.$s_sid; 
    //echo("WHERE ts BETWEEN '$tStart 00:00:00' AND '$tEnd 23:59:59'");
    $cond = "ts BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
    $g_dbM2->selectAllFromTable($stemp, $cond);
    $numRes = $g_dbM2->retrieveNumberOfResults();
    echo("Number of data for SID $s_sid: $numRes<br>");
    
    $g_dbM2->selectMaxValue($stemp,SXN_COLLECTOR_DATA_COLUMN_VALUE,$cond);
	$numRes = $g_dbM2->retrieveNumberOfResults();
	if($numRes>0)
	{
		$data = $g_dbM2->retrieveResult();
        $ymax = $data[0]*1.05;
        echo("max=$data[0]<br>");
	}
    $g_dbM2->selectMinValue($stemp,SXN_COLLECTOR_DATA_COLUMN_VALUE,$cond);
	$numRes = $g_dbM2->retrieveNumberOfResults();
	if($numRes>0)
	{
		$data = $g_dbM2->retrieveResult();
        $ymin = $data[0]*0.95;
        echo("min=$data[0]<br>");
	}
    
//    $g_dbM2->selectMaxValue($stemp,SXN_GENERAL_COLUMN_TIMESTAMP);
//	$numRes = $g_dbM2->retrieveNumberOfResults();
//	if($numRes>0)
//	{
//		$data = $g_dbM2->retrieveResult();
//        $xmax = $data[0];
//        echo("max=$data[0]<br>");
//	}
//    $g_dbM2->selectMinValue($stemp,SXN_GENERAL_COLUMN_TIMESTAMP);
//	$numRes = $g_dbM2->retrieveNumberOfResults();
//	if($numRes>0)
//	{
//		$data = $g_dbM2->retrieveResult();
//        $xmin = $data[0];
//        echo("mon=$data[0]<br>");
//	}
//    $minDate = new DateTime ($xmin);
//    $maxDate = new DateTime ($xmax);
//    $tsMin = $minDate->format(DateTime::ISO8601);
//    $tsMax = $maxDate->format(DateTime::ISO8601);
//    //$tsMin = $minDate->getTimestamp();
//    //$tsMax = $maxDate->getTimestamp();
//    //$delta = $tsMax - $tsMin;
//    echo(" $tsMin $tsMax <br>");
//    //$xmin = 0; $xmax = 2000;
}

$_SESSION['startDate'] = $startDate;
$_SESSION['endDate']   = $endDate; 
$_SESSION['xZoom'] = $xZoom; 
$_SESSION['yZoom'] = $yZoom;
$_SESSION['s_sid'] = $s_sid; 

?>


<!DOCTYPE html>
<head>
<meta charset="utf-8">
<style>

body {
  font: 10px sans-serif;
  margin: 50px;
}

.grid .tick {
	stroke: lightgrey;
	opacity: 0.7;
	shape-rendering: crispEdges;
}

.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.axis path {
	fill: none;
	stroke: #bbb;
	shape-rendering: crispEdges;
}

.line {
  fill: none;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.axis text {
	fill: #555;
}

.dot {
	/* consider the stroke-with the mouse detect radius? */
	stroke: transparent;
	stroke-width: 10px;  
	cursor: pointer;
}
 
.dot:hover {
	stroke: rgba(68, 127, 255, 0.3);
}

</style>

  
  <script src="../calendar/jquery-ui-1.11.4.custom/external/jquery/jquery.js"></script>
  <script src="../calendar/jquery-ui-1.11.4.custom/jquery-ui.js"></script>
  <link rel="stylesheet" href="../calendar/jquery-ui-1.11.4.custom/jquery-ui.css">
  <script>
  $(function() {
    $( "#startDate" ).datepicker();
  });
  $(function() {
    $( "#endDate" ).datepicker();
  });
  </script>
    
</head>

<body>
<?php
 echo("Zoom ");
 if($xZoom==1)
     echo("X <a href=\"sxn_presentation.php?do=xzoom&xZoom=2\">ON</a>");
 else
    echo("X <a href=\"sxn_presentation.php?do=xzoom&xZoom=1\">OFF</a>");
 if($yZoom==1)
     echo(" Y <a href=\"sxn_presentation.php?do=yzoom&yZoom=2\">ON</a>");
 else
    echo(" Y <a href=\"sxn_presentation.php?do=yzoom&yZoom=1\">OFF</a> "); 

     
 echo("   <a href=\"sxn_presentation.php?do=today\">Today</a><br>"); 

 ?>

   
<form name="myForm" action="sxn_presentation.php" onsubmit="return validateForm()" method="get"> 
<?php
echo(" From <input type=\"text\" id=\"startDate\" name=\"date1\" value=\"$startDate\" />");
echo(" To <input type=\"text\" id=\"endDate\" name=\"date2\" value=\"$endDate\" />");
?>
<input type="submit" name="submit" value="Submit" />
</form>

   
    
    
<script src="../d3/d3.min.js"></script>


<script>   
var gdata; 

var colors = [
	'steelblue',
	'green',
	'red',
	'purple'
]
var margin = {top: 20, right: 20, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;
    

//var format = d3.time.format("%Y-%m-%d %H:%M:%S");

var parseDate = d3.time.format("%Y-%m-%d %H:%M:%S").parse;



<?php
//echo("
// var x = d3.scale.linear()
//    .domain([$xmin,$xmax])
//");

//alert("benny");
//$tsMin = 'Wed Dec 01 2015 11:11:17 GMT+0100(CEST)';
//$tsMax = 'Wed Dec 04 2015 11:11:17 GMT+0100(CEST)';
//$tsMin = '2015-12-01 11:11:17';
//$tsMax = '2015-12-05 11:11:17';
//$tsMin = '2015-05-16';
//$tsMax = '2015-05-17';
$tsMin = $startDate.' 00:00:00';
$tsMax = $endDate.' 23:59:59';
//echo("$tsMin $tsMax");
echo("
 var x = d3.time.scale()
    .domain([new Date('$tsMin'),new Date('$tsMax')])
");
?>
    .range([0, width]);


<?php
echo("
var y = d3.scale.linear()
    .domain([$ymin,$ymax])
");
?>
    .range([height, 0]);
	

var xAxis = d3.svg.axis()
    .scale(x)
	.tickSize(-height)
	.tickPadding(10)	
	.tickSubdivide(true)	
    //.orient("bottom").ticks(3);
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
	.tickPadding(10)
	.tickSize(-width)
	.tickSubdivide(true)	
    .orient("left");

<?php
echo("var zoom = d3.behavior.zoom()");
if($xZoom == 1)echo("    .x(x)");
if($yZoom == 1)echo("    .y(y)");
    //.scaleExtent([1, 50])
 echo("   .on(\"zoom\", zoomed);");
?>
    
var line = d3.svg.line()
    .interpolate("linear")	
<?php
echo("
    .x(function(d) { return x(d.$jsonX); })
    .y(function(d) { return y(d.$jsonY); });	
    ");
?>

//************************************************************
// Generate our SVG object
//************************************************************	

var svg = d3.select("body").append("svg")
  .attr("width", width + margin.left + margin.right)
  .attr("height", height + margin.top + margin.bottom) 
  .call(zoom)
  .append("g")
  .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

<?php

echo("var url = \"sql.php?sid=$s_sid&startDate=$startDate&endDate=$endDate\";");
?>
     
d3.json(url, function(error, json) 
{
   if (error) throw error;
   gdata = json;
<?php
echo("
   gdata.forEach(function(d) {
    d.$jsonX = parseDate(d.$jsonX);
   });
   ");
?>
    
   //x.domain(d3.extent(gdata, function(d) { return d.ts; }));
   //y.domain([0, d3.max(gdata, function(d) { return d.nb_value; })]);
    
   // var tsi = d3.max(gdata, function(d) { return d.ts; })
    //var tsx = d3.min(gdata, function(d) { return d.ts; })
    
    //alert(d3.extent(gdata, function(d) { return d.ts; }));
    //alert(d3.extent(gdata, function(d) { return d.nb_value; }));
    
    
   svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

   svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);
  
   svg.append("g")
        .attr("class", "y axis")
	.append("text")
	.attr("class", "axis-label")
	.attr("transform", "rotate(-90)")
	.attr("y", (-margin.left) + 10)
	.attr("x", -height/2)
<?php
	echo(".text('$yLabel');");
?>

   svg.append("path")
        .attr("class", "line")
	.attr("clip-path", "url(#clip)")
        .attr("d", line(gdata));
});


//************************************************************
// Zoom specific updates
//************************************************************
function zoomed() {
	svg.select(".x.axis").call(xAxis);
	svg.select(".y.axis").call(yAxis);   
	svg.select('path.line').attr('d', line(gdata)); 
    //d3.select("#footer span").text("U.S. Commercial Flights, " + x.domain().map(format).join("-"));
}

</script>

<?php
//=============================================================
//
//
//=============================================================

//if($s_sid)
//{
//$query = "SELECT * from ".SXN_COLLECTOR_TABLE_DATA_PREFIX.$s_sid;
//echo("$query<br>");
//$g_dbM2->selectFromQuery($query);
//
//while($data = $g_dbM2->retrieveResult())
//{
//    $temp = $data[SXN_COLLECTOR_DATA_COLUMN_VALUE]; 
//    $temp = $temp + 10;
//    //echo("$temp,");
//}
//}
   readDataTypes(); 
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS, "");
   $numRes = $g_dbM1->retrieveNumberOfResults();
   echo("<br>Number of SIDs: $numRes<br>");
   echo("<table border=\"1\">");
     echo("<tr>");
     echo("<td>SID</td> ");
     echo("<td>TYPE</td>");
     echo("<td>UNIT</td>");
     echo("<td>TITLE</td>");
     echo("<td>TAG</td>");
     echo("<td>DESCRIPTION</td>");
     echo("<td>OWNER UID</td>");
     echo("<td>PERMISSION</td>");
     echo("<td>TIMESTAMP</td>");
     //echo("<td>Action</td>");
     echo("</tr>");
 
   while($data = $g_dbM1->retrieveResult())
   {
    $id          = $data[SXN_GENERAL_COLUMN_ID];
    $sid         = $data[SXN_ADMIN_STREAMS_COLUMN_SID];
    $type        = $data[SXN_ADMIN_STREAMS_COLUMN_TYPE];
    $unit        = $data[SXN_ADMIN_STREAMS_COLUMN_UNIT];
    $title       = $data[SXN_ADMIN_STREAMS_COLUMN_TITLE];
    $tag         = $data[SXN_ADMIN_STREAMS_COLUMN_TAG];
    $description = $data[SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION];
    $owner_uid   = $data[SXN_ADMIN_STREAMS_COLUMN_OWNERUID];
    $permission  = $data[SXN_ADMIN_STREAMS_COLUMN_PERMISSION];
    $ts          = $data[SXN_GENERAL_COLUMN_TIMESTAMP];
    
     //$data_name = $g_data_type_map[$dt];
    
     echo("<tr>");
     echo("<td><a href=\"sxn_presentation.php?do=select_sid&sid=$sid\">$sid </a></td> ");
     echo("<td>$type</td>");
     echo("<td>$g_data_name[$unit] ($g_data_unit[$unit])</td>");
     echo("<td>$title</td>");
     echo("<td>$tag</td>");
     echo("<td>$description</td>");
     echo("<td>$owner_uid</td>");
     echo("<td>$permission</td>");
     echo("<td>$ts</td>");
     //echo("<td><a href=\"sxn_config.php?do=del_sid&a_id=$id\">delete</a></td>");
     echo("</tr>");
   }
   echo("</table>");
   system("ls *.ip > ipList.work");
   lib_listIpFiles("ipList.work");
   
?>

	</body>
</html>

