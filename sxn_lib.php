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
function readDataTypes()
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
    //echo $data[SXN_ADMIN_DATATYPES_COLUMN_NAME]."-".$data[SXN_ADMIN_DATATYPES_COLUMN_UNIT]."<br>";
    //$g_data_id[$ii]   = $data[SXN_ADMIN_DATATYPES_COLUMN_DATATYPEID];
    $g_data_name[$ii] = $data[SXN_ADMIN_DATATYPES_COLUMN_NAME];
    $g_data_unit[$ii] = $data[SXN_ADMIN_DATATYPES_COLUMN_UNIT];
    $g_data_type_map[$ii] = $data['id'];
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
?>
    