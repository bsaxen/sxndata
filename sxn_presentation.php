
<?php


require_once('sxn_lib.php');
require_once('sxn_definition.php');

  $g_dbM1 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_ADMIN);
  $g_dbM2 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_COLLECTOR);
  $g_dbM3 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_CONTROL);


$do = (isset($_GET['do']) ? $_GET['do'] : null);

if($do=="select_sid")
{
 $s_sid = $_GET['sid'];   
}

$xmin = 0; $xmax = 2000;
$ymin = 0; $ymax = 50;
?>

<!DOCTYPE html>
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

<body>
    
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

var parseDate = d3.time.format("%d-%b-%y").parse;



<?php
echo("
 var x = d3.scale.linear()
    .domain([$xmin,$xmax])
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


var zoom = d3.behavior.zoom()
    .x(x)
    .y(y)
    //.scaleExtent([1, 50])
    .on("zoom", zoomed);

var line = d3.svg.line()
    .interpolate("linear")	
    .x(function(d) { return x(d.id); })
    .y(function(d) { return y(d.nb_value); });			
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
//echo("var url = \"http://localhost/sxndata/sql.php?sid=$s_sid\";");
echo("var url = \"sql.php?sid=$s_sid\";");
?>
     
d3.json(url, function(error, json) 
{
   if (error) throw error;
   gdata = json;
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
	.text('Electric kWh');	

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
}

</script>

<?php
//=============================================================
//
//
//=============================================================
echo("Selected SID=$s_sid<br>");

if($s_sid)
{
$query = "SELECT * from ".SXN_COLLECTOR_TABLE_DATA_PREFIX.$s_sid;
echo("$query<br>");
$g_dbM2->selectFromQuery($query);

while($data = $g_dbM2->retrieveResult())
{
    $temp = $data[SXN_COLLECTOR_DATA_COLUMN_VALUE]; 
    $temp = $temp + 10;
    //echo("$temp,");
}
}

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
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS, "");
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
     echo("<td>$unit</td>");
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

?>

	</body>
</html>

