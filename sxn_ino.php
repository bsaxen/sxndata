<?php
$order  = $_GET['order'];

if($order == "build")
{
	echo("Build...<br>");
	system("ls;ino build;",$retval);
	echo($retval);
	echo("<br>");
}
if($order == "clean")
{
	echo("Clean... <br>");
	system("pwd;ino clean;",$retval);
	echo($retval);
	echo("<br>");
}

echo("<a href=\"index.php?order=build\">Arduino build</a><br>");
echo("<a href=\"index.php?order=clean\">Arduino clean</a><br>");

?>
