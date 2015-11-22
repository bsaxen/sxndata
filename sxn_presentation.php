<!doctype html>

<html>
	<head>
		<title>Line Chart</title>
		<script src="../Chart.js-master/Chart.js"></script>
	</head>
	<body>
		<div style="width:80%">
			<div>
				<canvas id="canvas" height="450" width=600"></canvas>
			</div>
		</div>


	<script>


		var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
		var lineChartData = {
                                 
<?php
require_once('sxn_lib.php');
require_once('sxn_definition.php');

  $g_dbM1 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_ADMIN);
  $g_dbM2 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_COLLECTOR);
  $g_dbM3 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_CONTROL);
//================================================================
$lim = 100;
echo("labels : [");
for($ii=1;$ii<$lim;$ii++)
{
    if($ii%100 == 0)
      echo("$ii,");
    else 
      echo("\" \",");
}
echo("\".\"],");
//================================================================
?>
			datasets : [
				{
					label: "My First dataset",
					fillColor : "rgba(220,220,220,0.2)",
					strokeColor : "rgba(220,220,220,1)",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#f0f",
					pointHighlightFill : "#0ff",
					pointHighlightStroke : "rgba(220,220,220,1)",
<?php
echo("data : [");
for($ii=1;$ii<$lim;$ii++)echo("randomScalingFactor(),");
echo("randomScalingFactor()]");
//================================================================
echo("				},");
?>
				{
					label: "My Second dataset",
					fillColor : "rgba(151,187,205,0.2)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#ff0",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(151,187,205,1)",
<?php

echo("data : [");
for($ii=1;$ii<$lim;$ii++)echo("randomScalingFactor(),");
echo("randomScalingFactor()]");
//===============================================================
echo("				}");
?>
			]

		}

	window.onload = function(){
		var ctx = document.getElementById("canvas").getContext("2d");
		window.myLine = new Chart(ctx).Line(lineChartData, {
		        animationSteps: 10,
                	responsive: true,
                        animation: true,
                        scaleShowGridLines : true,
                        bezierCurve: true,
                        pointDot: true,
                        scaleGridLineWidth: 1,
                        showTooltips: true,
                        tooltipCaretSize: 1,
                        pointHitDetectionRadius: 1,
                        pointDotRadius: 1
		});
	}
       </script>
<?php

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
     echo("<td>Action</td>");
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
     echo("<td><a href=\"sxn_config.php?do=upd_sid&a_id=$id\">$sid </a></td> ");
     echo("<td>$type</td>");
     echo("<td>$unit</td>");
     echo("<td>$title</td>");
     echo("<td>$tag</td>");
     echo("<td>$description</td>");
     echo("<td>$owner_uid</td>");
     echo("<td>$permission</td>");
     echo("<td>$ts</td>");
     echo("<td><a href=\"sxn_config.php?do=del_sid&a_id=$id\">delete</a></td>");
     echo("</tr>");
   }
   echo("</table>");


$s_id = 901;
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS, SXN_ADMIN_STREAMS_COLUMN_SID." = $s_id");
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
  }
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
              <input name=\"f_action\" type=\"hidden\" value=\"updSidForm\" />
              <input name=\"f_id\" type=\"hidden\" value=\"$id\" />
              <tr><td>SID</td><td><input name=\"f_sid\" type=\"text\" size=\"50\" value=\"$sid\"/></td></tr> 
              <tr><td>Data Type</td><td><select name=\"f_type\"> ");
              $selected = '';
              for($ii=1;$ii<=count($g_data_id);$ii++)
              {
                if($dt == $g_data_name) $selected = 'SELECTED';
                    echo("<option value=\"$g_data_id[$ii]\" $selected>$g_data_name[$ii] ($g_data_unit[$ii]) </option>");
              }
              echo(" </select></td></tr>
              <tr><td>Title      </td><td><input name=\"f_title\"       type=\"text\" size=\"50\"  value=\"$title\"/></td></tr>
              <tr><td>Tag        </td><td><input name=\"f_tag\"         type=\"text\" size=\"50\"  value=\"$tag\"/></td></tr>
              <tr><td>Description</td><td><input name=\"f_description\" type=\"text\" size=\"50\"  value=\"$description\"/></td></tr> 
              <tr><td>OwnerId    </td><td><input name=\"f_owner_uid\"   type=\"text\" size=\"50\"  value=\"$owner_uid\"/></td></tr>        
              <tr><td>Permission </td><td><input name=\"f_permission\"  type=\"text\" size=\"50\"  value=\"$permission\"/></td></tr>           
              <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Update SID\" /></td><td></td></tr>
        </form></table>
  ");

echo("Benny<br>");
?>

	</body>
</html>

