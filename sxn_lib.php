<?php
$g_servername = "localhost";
$g_username =   SXN_USER; 
$g_password =   SXN_PASSWORD;

$g_users      = array();      // table SXN_users
$g_sids       = array();      // table SXN_streams
$g_data_id    = array();      // table SXN_data_types
$g_data_name  = array();      // table SXN_data_types
$g_data_unit  = array();      // table SXN_data_types
$g_heartbeat_units = array(); // no table */
$g_heartbeat_units[1] = 'second';
$g_heartbeat_units[2] = 'minute';
$g_heartbeat_units[3] = 'hour';
$g_heartbeat_units[4] = 'h24';
$g_heartbeat_units[5] = 'auto';
$g_command_type_id   = array();
$g_command_type_name = array();

//=======================================
function lib_listFileContent($filename)
//======================================= 
{
 
    
    $handle = fopen($filename, "r");
    if ($handle) 
    { 
        while (($line = fgets($handle)) !== false) 
        {
          sscanf($line,"%s %s %s",$ip,$yymmdd,$hhmmss);
          $stemp = $yymmdd.' '. $hhmmss;
          $now = date("Y-m-d H:i:s"); 
          //$stemp = '2015-12-27 12:10:12';
          //$now = '2015-12-31 16:15:15';            
          $start   = strtotime( $stemp );
          $end     = strtotime( $now );
          $temp    = $end - $start;  
          $days    = floor($temp/86400);$rest = $temp%86400;
          $hours   = floor($rest/3600);$rest = $temp%3600;
          $minutes = floor($rest/60);$seconds = $temp%60;
          $nmin    = floor($temp/60); 
          if($nmin >= 0)
          {
                if($nmin < 60)echo("<p style=\"color:#00CD00\">$filename [$ip] Online($nmin)</p>");
                if($nmin >= 60)
                {
                    echo("<p style=\"color:#0099ff\">$filename [$ip]  Offline($days days $hours hours $minutes minutes) $stemp</p>");
                }
          }
          else
                if($nmin < 0)echo("<p style=\"color:#CD0000\">$filename [$ip] Error($nmin)</p>");
            
        }
        fclose($handle);
    } 
    else
        echo("Error lib_listFileContent($filename)");
}
//=======================================
function lib_listIpFiles($filename)
//======================================= 
{
    $now = date("Y-m-d H:i:s"); 
    echo("<h2>Client Status $now</h2>");
    $handle = fopen($filename, "r");
    if ($handle) 
    {    
        while (($line = fgets($handle)) !== false) 
        {
          $line = trim($line);
          //echo("file=$line<br>");
          lib_listFileContent($line);
        }
        fclose($handle);
    } 
}
//=======================================
function lib_log($logname,$item)
//======================================= 
{
   $filename = $logname.'.log';
   if (file_exists($filename))
   {
      $fh = fopen($filename, "a");
      fwrite($fh, $item);
      fclose($fh);
   }
    else
     {
      $fh = fopen($filename, "w");
      fwrite($fh, $item);
      fclose($fh);
      chmod($filename,0777);
     }             
}
//=======================================
function lib_remember($memid,$item)
//======================================= 
{
   $filename = $memid.'.mem';
   if (file_exists($filename))
   {
      $fh = fopen($filename, "w");
      fwrite($fh, $item);
      fclose($fh);
   }
    else
     {
      $fh = fopen($filename, "w");
      fwrite($fh, $item);
      fclose($fh);
      chmod($filename,0777);
     }              
}
//=======================================
function lib_recall($memid)
//======================================= 
{
   $data = 'void';
   $filename = $memid.'.mem';
   if (file_exists($filename)) 
   {
      $fh = fopen($filename, "r");
      $data = fread($fh,filesize($filename));
      fclose($fh);
   }   
   return($data);
}
//=======================================
function getExtension($str)
//======================================= 
{
  $i = strrpos($str,".");
  if (!$i) { return ""; }
  $l = strlen($str) - $i;
  $ext = substr($str,$i+1,$l);
  return $ext;
}
//=======================================
function safeText($text)
//=======================================
{
   $text = str_replace("#", "No.", $text); 
   $text = str_replace("$", "Dollar", $text); 
   $text = str_replace("%", "Percent", $text); 
   $text = str_replace("^", "", $text); 
   $text = str_replace("&", "and", $text); 
   $text = str_replace("*", "", $text); 
   $text = str_replace("?", "", $text); 
   return($text);
}
//=======================================
function check_file_uploaded_name ($filename)
//=======================================
{
    (bool) ((preg_match("`^[-0-9A-Z_\.]+$`i",$filename)) ? true : false);
}

//=======================================
function uploadFile()
//=======================================
{
  global $docRoot;
  define ("MAX_SIZE","3000");
  $errors=0;
  $newname = '';
  
  if(isset($_POST['submit_file']))
    {
      $import=$_FILES['import_file']['name'];
      if ($import)// && check_file_uploaded_name ($import))
        {
          $file_name = stripslashes($_FILES['import_file']['name']);
          $file_name = safeText($file_name);
          $extension = getExtension($file_name);
          $extension = strtolower($extension);
          if ($extension != "nbc")
            {
              echo "<h1>Unknown Import file Extension: $extension</h1>";
              $errors=1;
            }
          else
            {
              $size=filesize($_FILES['import_file']['tmp_name']);
              if ($size > MAX_SIZE*1024)
                {
                  echo "<h1>You have exceeded the size limit! $size</h1>";
                  $errors=1;
                }
              //$image_name=time().'.'.$extension;
              //$file_name = $db.'-'.$id.'.'.$extension;
              
              $newname=$docRoot."/sxndata/".$file_name;
              //echo("New name $newname<br>");
              $copied = move_uploaded_file($_FILES['import_file']['tmp_name'], $newname);
              if (!$copied)
                {
                  echo "<h1>Import Copy unsuccessfull! $size</h1>";
                  $errors=1;
                }
            }
        }
    }

  if($errors)$newname = "error";
  if(isset($_POST['submit_file']) && !$errors)
    {
      chmod($newname,0666);
      echo "<h1>File Uploaded Successfully! $size</h1>";
    }

  return($newname); 
}
//======================================================================
function lib_readDataTypes()
//======================================================================
{
  global $g_data_name,$g_data_unit,$g_data_type_map;
  global $g_dbM1;

  if($g_dbM1)
  {
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_DATATYPES, "");
   $ii = 0;
   while($data = $g_dbM1->retrieveResult())
   {
    $ii++;
    //echo $data[SXN_ADMIN_DATATYPES_COLUMN_NAME]."-".$data[SXN_ADMIN_DATATYPES_COLUMN_UNIT]." $ii<br>";
    //$g_data_id[$ii]   = $data[SXN_ADMIN_DATATYPES_COLUMN_DATATYPEID];
    $g_data_name[$ii] = $data[SXN_ADMIN_DATATYPES_COLUMN_NAME];
    $g_data_unit[$ii] = $data[SXN_ADMIN_DATATYPES_COLUMN_UNIT];
    $g_data_type_map[$ii] = $data[SXN_GENERAL_COLUMN_ID];
   }
  }
  else
	echo("Datatypes database does not exist<br>");
}

//======================================================================
function readCommandTypes()
//======================================================================
{
  global $g_command_type_id,$g_command_type_name,$g_command_type_map;
  global $g_dbM1;

  if($g_dbM1)
  {
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_COMMANDTYPES, "");
   $ii = 0;
   while($data = $g_dbM1->retrieveResult())
   {
    $ii++;
    $g_command_type_name[$ii]        = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_NAME];
    $g_command_type_id[$ii]          = $data[SXN_GENERAL_COLUMN_ID];
    $g_command_type_description[$ii] = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION];
    $g_command_type_map[$g_command_type_id[$ii]] = $g_command_type_name[$ii];
   }
  }
  else
	echo("Commandtypes database does not exist<br>");
}
//======================================================================
function lib_getLatestValue($sid) 
//======================================================================
{
    global $g_dbM2;
    $sql = "SELECT * FROM ".SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid." ORDER BY ".SXN_GENERAL_COLUMN_ID." DESC  LIMIT 1";
    $g_dbM2->selectFromQuery($sql);
    $numRes = $g_dbM2->retrieveNumberOfResults();
    if($numRes>0)
    { 
            while($data = $g_dbM2->retrieveResult())
		    {
               $par = SXN_COLLECTOR_DATA_COLUMN_VALUE;
		       $value = $data[$par];
               return($value);
            }
	}
    return(SXN_NO_VALUE);
}
//======================================================================
function lib_getLatestDerivative($sid) 
//======================================================================
{
    global $g_dbM2;
    $sql = "SELECT * FROM ".SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid." ORDER BY ".SXN_GENERAL_COLUMN_ID." DESC  LIMIT 2";
    $g_dbM2->selectFromQuery($sql);
    $numRes = $g_dbM2->retrieveNumberOfResults();
    if($numRes>0)
    { 
            $par1 = SXN_COLLECTOR_DATA_COLUMN_VALUE;
            $par2 = SXN_GENERAL_COLUMN_TIMESTAMP;
            $ix = 0;
            while($data = $g_dbM2->retrieveResult())
		    {
               $ix++;
		       $value[$ix] = $data[$par1];
               $timed[$ix] = strtotime($data[$par2]);
            }
            $res  = ($value[1] - $value[2])/($timed[1] - $timed[2]);
            return($res);
	}
    return(SXN_NO_VALUE);
}
//======================================================================
function executeSidFunction($sid)
//======================================================================
{
  // check if any sid function is set active in database
    $sFunc = 'void';
    //$sfunc = checkSidFunction($sid);
    //if($sid == 999)$sFunc = 'controlSaxenHeater'; // TBD read this from database
    //echo("ecexute $sFunc<br>");
    if($sFunc != 'void')call_user_func(array($sFunc,'doIt'));   
      //if($sFunc != 'void')call_user_func(array($sFunc));   
        
}

//======================================================================
function insertOrder($sid,$order)
//======================================================================
{
   global $g_dbM1,$g_dbM3;
   if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_STREAMS,SXN_ADMIN_STREAMS_COLUMN_SID,$sid) == true)
   {
    $valueArray  = array($sid,$order,'new');
    $columnArray = array(SXN_CONTROL_COMMANDS_COLUMN_SID,
                        SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
                        SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    if($sid) $g_dbM3->insertRow(SXN_CONTROL_TABLE_COMMANDS,$columnArray,$valueArray);
   }
}
?>
    