<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="Your description goes here" />
	<meta name="keywords" content="your,keywords,goes,here" />
	<meta name="author" content="Your Name" />
	<link rel="stylesheet" type="text/css" href="sxndata.css" title="Variant Duo" media="screen,projection" />
	<title>SXN Data Admin</title>
<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
//echo("START ");

require_once('sxn_lib.php');
require_once('sxn_definition.php');
echo("</head> ");
$docRoot = $_SERVER['DOCUMENT_ROOT'];
//echo("<br>$docRoot<br>");

$do = (isset($_GET['do']) ? $_GET['do'] : null);

$s_id  = (isset($_GET['a_id'])? $_GET['a_id']: null);
$s_sid = (isset($_GET['sid']) ? $_GET['sid'] : null);
$s_uid = (isset($_GET['uid']) ? $_GET['uid'] : null);
$s_cid = (isset($_GET['cid']) ? $_GET['cid'] : null);
$s_did = (isset($_GET['did']) ? $_GET['did'] : null);
//if($do)echo("$do<br>");

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

if($do != 'add_db')
{
  $g_dbM1 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_ADMIN);
  $g_dbM2 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_COLLECTOR);
  $g_dbM3 = new DataManager("root", "amazon", "localhost", SXN_DATABASE_CONTROL);
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

//======================================================================
function listNabtonDatabases()
//======================================================================
{
	global $g_servername,$g_username,$g_password;
    global $g_dbM1;
	
//	//$link = mysql_connect($g_servername, $g_username, $g_password);
//    //if (!$link) 
//    //{
//    //   die('Could not connect: ' . mysql_error());
//    //}    
//    $res = $g_dbM1->mysqli_->query("SHOW DATABASES");
//    while ($row1 = mysql_fetch_assoc($res)) 
//    {
//	  $db = $row1['Database']; 
//	  if(strstr($db,"NB") != NULL)
//	  {
//         echo "<h1>$db</h1><br>";
//         mysql_select_db($db);
//         $sql = "SHOW TABLES FROM $db";
//         $result1 = mysql_query($sql);
//         while ($row2 = mysql_fetch_row($result1)) 
//         {
//			$table = $row2[0];
//			//echo "XTable: {$row2[0]}\n";
//            echo "<h2>$table</h2><br>";              
//            $sql = "SHOW COLUMNS FROM $table";
//            $result2 = mysql_query($sql);
//            
//            if (!$result2) 
//            {
//              echo 'Could not run query: ' . mysql_error();
//              exit;
//            }
//
//            if (mysql_num_rows($result2) > 0) 
//            {
//               while ($row3 = mysql_fetch_assoc($result2)) 
//               {
//                  print_r($row3); echo("<br>");
//               }
//            }
//            mysql_free_result($result2); 
//         }
//         mysql_free_result($result1);        
//      }
//    }
//    mysql_close($link);
    echo("Not supported<br>");
}

//======================================================================
function addDataType($name,$unit)
//======================================================================
{
   global $g_dbM1;
   
   if($name && $unit)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_DATATYPES,SXN_ADMIN_DATATYPES_COLUMN_NAME,$name) == false)
      {
        $valueArray  = array($name,
                             $unit);

        $columnArray = array(SXN_ADMIN_DATATYPES_COLUMN_NAME,
                             SXN_ADMIN_DATATYPES_COLUMN_UNIT); 
        $g_dbM1->insertRow(SXN_ADMIN_TABLE_DATATYPES, $columnArray , $valueArray);
      }
      else
        echo("DataType $name already exists<br>");     
   } 
   else
   {
      echo("No action!");
   }
}

//======================================================================
function dropNabtonDatabases()
//======================================================================
{
  global $g_servername,$g_username,$g_password;
  
    $link=new mysqli($g_servername,$g_username,$g_password) or die(".........");

    $db_nabton[1] = SXN_DATABASE_COLLECTOR;
    $db_nabton[2] = SXN_DATABASE_CONTROL;
    $db_nabton[3] = SXN_DATABASE_ADMIN;
    
    for($ii=1;$ii<=3;$ii++)
    {
	   $db_name = $db_nabton[$ii];
       $sql = "DROP DATABASE $db_name";
       if ($link->query($sql)) 
       {
        echo "Database $db_name dropped successfully<br>";
       } 
       else 
       {
        echo "Error dropping database: $db_name ". $link->error ."<br>";
       }
    }     
    $link->close();
}
//======================================================================
function createNabtonDatabases()
//======================================================================
{
	global $g_servername,$g_username,$g_password;
    
	$link=new mysqli($g_servername,$g_username,$g_password) or die(".........");
	//$link = mysql_connect($g_servername, $g_username, $g_password);
//    if (!$link) 
//    {
//       die('Could not connect: ' . mysql_error());
//    }
    
    $db_nabton[1] = SXN_DATABASE_COLLECTOR;
    $db_nabton[2] = SXN_DATABASE_CONTROL;
    $db_nabton[3] = SXN_DATABASE_ADMIN;
    
    for($ii=1;$ii<=3;$ii++)
    {
	   $db_name = $db_nabton[$ii];
       $sql = "CREATE DATABASE $db_name";
       if ($link->query($sql)) 
       {
        echo "Database $db_name created successfully<br>";
       } 
       else 
       {
        echo "Error creating database: $db_name ". $link->error ."<br>";
       }
    }    

    
    $link->select_db(SXN_DATABASE_CONTROL) or die('Could not select database');
    $sql = sprintf("CREATE TABLE %s (
    id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    %s INT(6) UNSIGNED,
    %s VARCHAR(80),
    %s VARCHAR(12),
    ts TIMESTAMP)",
    SXN_CONTROL_TABLE_COMMANDS,
    SXN_CONTROL_COMMANDS_COLUMN_SID,
    SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
    SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
    
    $link->select_db(SXN_DATABASE_ADMIN) or die('Could not select database');
    $sql = sprintf("CREATE TABLE %s (
    id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    %s INT(6) UNSIGNED,
    %s VARCHAR(64),
    %s VARCHAR(64),
    ts TIMESTAMP)",
    SXN_ADMIN_TABLE_USERS,
    SXN_ADMIN_USERS_COLUMN_UID,
    SXN_ADMIN_USERS_COLUMN_NAME,
    SXN_ADMIN_USERS_COLUMN_PASSWORD);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }

    $sql = sprintf("CREATE TABLE %s (
            id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            %s INT(6) UNSIGNED,
            %s INT(3) UNSIGNED,
            %s VARCHAR(80),
            %s VARCHAR(80),
            %s VARCHAR(80),
            %s TEXT,
            %s INT(6) UNSIGNED,
            %s INT(1) UNSIGNED,
            ts TIMESTAMP)",
            SXN_ADMIN_TABLE_STREAMS,
            SXN_ADMIN_STREAMS_COLUMN_SID,
            SXN_ADMIN_STREAMS_COLUMN_TYPE,
            SXN_ADMIN_STREAMS_COLUMN_UNIT,
            SXN_ADMIN_STREAMS_COLUMN_TITLE,
            SXN_ADMIN_STREAMS_COLUMN_TAG,
            SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION,
            SXN_ADMIN_STREAMS_COLUMN_OWNERUID,
            SXN_ADMIN_STREAMS_COLUMN_PERMISSION);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
    $sql = sprintf("CREATE TABLE %s (
            id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            %s INT(6) UNSIGNED,
            %s INT(6) UNSIGNED,
            ts TIMESTAMP)",
            SXN_ADMIN_TABLE_APPSTREAMLINK,
            SXN_ADMIN_APPSTREAMLINK_COLUMN_APPID,
            SXN_ADMIN_APPSTREAMLINK_COLUMN_SID);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
    $sql = sprintf("CREATE TABLE %s (
            id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            %s VARCHAR(80),
            %s VARCHAR(80),
            ts TIMESTAMP)",
            SXN_ADMIN_TABLE_DATATYPES,
            SXN_ADMIN_DATATYPES_COLUMN_NAME,
            SXN_ADMIN_DATATYPES_COLUMN_UNIT);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
     $sql = sprintf("CREATE TABLE %s (
            id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            %s VARCHAR(80),
            %s TEXT,
            ts TIMESTAMP)",
            SXN_ADMIN_TABLE_COMMANDTYPES,
            SXN_ADMIN_COMMANDTYPES_COLUMN_NAME,
            SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
    $sql = sprintf("CREATE TABLE %s (
            id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            %s VARCHAR(80),
            %s INT(6) UNSIGNED,
            %s INT(1) UNSIGNED,
            %s INT(2) UNSIGNED,
            ts TIMESTAMP)",
            SXN_ADMIN_TABLE_APPLICATIONS,
            SXN_ADMIN_APPLICATIONS_COLUMN_TITLE,
            SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID,
            SXN_ADMIN_APPLICATIONS_COLUMN_SHARED,
            SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE);
    if ($link->query($sql)) {
      echo "$sql<br>";
    } else {
      echo 'Error executing query: ' . $link->error . "<br>";
    }
    
    $link->close();
}
//======================================================================
function addUser($name,$pswd,$uid)
//======================================================================
{
    echo("$name,$pswd,$uid");
   global $g_dbM1;
   if($name && $pswd && $uid)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_USERS,SXN_ADMIN_USERS_COLUMN_NAME,$name) == false)
      {
        $valueArray  = array($name,
                              $uid,
                             $pswd);

        $columnArray = array(SXN_ADMIN_USERS_COLUMN_NAME,
                             SXN_ADMIN_USERS_COLUMN_UID,
                             SXN_ADMIN_USERS_COLUMN_PASSWORD); 
        $g_dbM1->insertRow(SXN_ADMIN_TABLE_USERS, $columnArray , $valueArray);
      }
      else
        echo("User already exists<br>");
        
   } 
   else
   {
      echo("No action!");
   }
}
//======================================================================
function updateUser($id,$name,$pswd,$uid)
//======================================================================
{
   global $g_dbM1;
   if($name && $pswd && $uid && $id)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_USERS,'id',$id) == true)
      {
        $valueArray  = array($name,
                              $uid,
                             $pswd);

        $columnArray = array(SXN_ADMIN_USERS_COLUMN_NAME,
                             SXN_ADMIN_USERS_COLUMN_UID,
                             SXN_ADMIN_USERS_COLUMN_PASSWORD); 
        $g_dbM1->updateRow(SXN_ADMIN_TABLE_USERS,$columnArray,$valueArray,"id = $id");
      }
      else
        echo("User does not exist<br>");
   } 
   else
   {
      echo("No action!");
   }
}
//======================================================================
function addApp($appid,$title,$ownerid,$charttype,$shared)                                                                                                            
//======================================================================
{
   global $g_dbM1;
   if($title && $ownerid && $charttype)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_APPLICATIONS,SXN_ADMIN_APPLICATIONS_COLUMN_APPID,$appid) == false)
      {
        $valueArray  = array($appid,
                             $title,
                             $ownerid,
                             $charttype,
                             $shared);

        $columnArray = array(SXN_ADMIN_APPLICATIONS_COLUMN_APPID,
			                 SXN_ADMIN_APPLICATIONS_COLUMN_TITLE,
                             SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID,
                             SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE,
                             SXN_ADMIN_APPLICATIONS_COLUMN_SHARED); 
        $g_dbM1->insertRow(SXN_ADMIN_TABLE_APPLICATIONS, $columnArray , $valueArray);
      }
      else
        echo("Application $appid already exists<br>");
   } 
   else
   {
      echo("No action!");
   }
}
//======================================================================
function addSid($sid,$type,$unit,$title,$tag,$description,$owner_uid,$permission)                                                                                                            
//======================================================================
{
	  if(!$type) $type = 1;
	  if(!$unit) $unit = 1;
	  if(!$title) $title = "no title";
	  if(!$tag) $tag = "no tag";	  
	  if(!$description) $description = "no description";
	  if(!$owner_uid)  $owner_uid = 1;
	  if(!$permission) $permission = 1;
	  	  	  	  
	  global $g_dbM1,$g_dbM2;
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_STREAMS,SXN_ADMIN_STREAMS_COLUMN_SID,$sid) == false)
      {

          $valueArray  = array($sid,
                           $type,
                           $unit,
                           $title,
                           $tag,
                           $description,
                           $owner_uid,
                           $permission);

          $columnArray = array(SXN_ADMIN_STREAMS_COLUMN_SID,
                               SXN_ADMIN_STREAMS_COLUMN_TYPE,
                               SXN_ADMIN_STREAMS_COLUMN_UNIT,
                               SXN_ADMIN_STREAMS_COLUMN_TITLE,
                               SXN_ADMIN_STREAMS_COLUMN_TAG,
                               SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION,
                               SXN_ADMIN_STREAMS_COLUMN_OWNERUID,
                               SXN_ADMIN_STREAMS_COLUMN_PERMISSION); 
          $g_dbM1->insertRow(SXN_ADMIN_TABLE_STREAMS, $columnArray , $valueArray);

          // Create Stream Table
          $g_dbM2->createSidTable($sid);
      }
      else
      {
         echo("SID already exists");
      }
}
//======================================================================
function updateApp($id,$title,$ownerid,$charttype,$shared)                                                                                                            
//======================================================================
{
   global $g_dbM1;	
      if($appid)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_APPLICATIONS,SXN_GENERAL_COLUMN_ID,$appid) == true)
      {
         $valueArray  = array($title,
                             $ownerid,
                             $charttype,
                             $shared);

        $columnArray = array(SXN_ADMIN_APPLICATIONS_COLUMN_TITLE,
                             SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID,
                             SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE,
                             SXN_ADMIN_APPLICATIONS_COLUMN_SHARED); 
        $g_dbM1->updateRow(SXN_ADMIN_TABLE_APPLICATIONS,$columnArray,$valueArray,"id = $id");
      }
      else
        echo("Application does not exist<br>");
   } 
   else
   {
      echo("No application found!");
   }
}
//======================================================================
function updateDataType($id,$name,$unit)                                                                                                            
//======================================================================
{
   global $g_dbM1;	
   if($name && $unit)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_DATATYPES,SXN_GENERAL_COLUMN_ID,$id) == true)
      {
          $valueArray  = array($name,
                               $unit);

          $columnArray = array(SXN_ADMIN_DATATYPES_COLUMN_NAME,
                               SXN_ADMIN_DATATYPES_COLUMN_UNIT); 
          $g_dbM1->updateRow(SXN_ADMIN_TABLE_DATATYPES,$columnArray,$valueArray,"id = $id");
      }
      else
        echo("Data Type does not exist $f_id");
   } 
   else
   {
      echo("No name and/or unit");
   }
}
//======================================================================
function updateSid($id,$sid,$type,$unit,$title,$tag,$description,$owner_uid,$permission)                                                                                                            
//======================================================================
{
   global $g_dbM1;	
   if($sid)
   {
      if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_STREAMS,SXN_ADMIN_STREAMS_COLUMN_SID,$sid) == true)
      {
		      $valueArray  = array($sid,
                           $type,
                           $unit,
                           $title,
                           $tag,
                           $description,
                           $owner_uid,
                           $permission);

          $columnArray = array(SXN_ADMIN_STREAMS_COLUMN_SID,
                               SXN_ADMIN_STREAMS_COLUMN_TYPE,
                               SXN_ADMIN_STREAMS_COLUMN_UNIT,
                               SXN_ADMIN_STREAMS_COLUMN_TITLE,
                               SXN_ADMIN_STREAMS_COLUMN_TAG,
                               SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION,
                               SXN_ADMIN_STREAMS_COLUMN_OWNERUID,
                               SXN_ADMIN_STREAMS_COLUMN_PERMISSION); 
          $g_dbM1->updateRow(SXN_ADMIN_TABLE_STREAMS,$columnArray,$valueArray,"id = $id");
      }
   } 
   else
   {
      echo("No action!");
   }
}

//======================================================================
function addCommandType($name,$description)                                                                                                            
//======================================================================
{
   global $g_dbM1;	
   // Default values
   if(!$name)          $name   = 'no command name';
   if(!$description)  $description   = 'no command description';
      
   if($name) 
   {
	  if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_COMMANDTYPES,SXN_ADMIN_COMMANDTYPES_COLUMN_NAME,$name) == false)
      {
        $valueArray  = array($name,
                             $description);

        $columnArray = array(SXN_ADMIN_COMMANDTYPES_COLUMN_NAME,
                             SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION); 
        $g_dbM1->insertRow(SXN_ADMIN_TABLE_COMMANDTYPES, $columnArray , $valueArray);
      }
      else
        echo("Command Type $name already exists<br>");
   }
   else
   {
     echo("No command name given!");
   }
}
//======================================================================
function updateCommandType($id,$name,$description)                                                                                                            
//======================================================================
{
   global $g_dbM1;	
   if($name)
   {
	  if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_COMMANDTYPES,SXN_GENERAL_COLUMN_ID,$id) == true)
      {
        $valueArray  = array($name,
                             $description);

        $columnArray = array(SXN_ADMIN_COMMANDTYPES_COLUMN_NAME,
                             SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION); 
        $g_dbM1->updateRow(SXN_ADMIN_TABLE_COMMANDTYPES,$columnArray,$valueArray,"id = $id");
      }
      else
        echo("Command Type $name does not exist ");
   } 
   else
   {
      echo("No command name!");
   }
}
//----------------------------------------------------------------------
// End Library
//----------------------------------------------------------------------
// Start POST
//----------------------------------------------------------------------
$action = "";
//$action = $_POST['f_action'];
$action = (isset($_POST['f_action']) ? $_POST['f_action'] : null);
if($action) 
{
    $f_id = (isset($_POST['f_id']) ? $_POST['f_id'] : null);
    $f_ts = (isset($_POST['f_ts']) ? $_POST['f_ts'] : null);
}

if($action == 'addSidForm')
{
   $sid         = $_POST['f_sid'];
   $type        = $_POST['f_type'];
   //$unit        = $_POST['f_unit'];
   $unit        = (isset($_GET['f_unit'])? $_GET['f_unit']: null);
   $title       = $_POST['f_title'];
   $tag         = $_POST['f_tag'];
   $description = $_POST['f_description'];
   $owner_uid   = $_POST['f_owner_uid'];
   $permission  = $_POST['f_permission'];
   
      
   if($sid)
   {
	   // Default values
	  if(!$type)       $type        = 1;
      if(!$unit)       $unit        = 1;
	  if(!$title)      $title       = 'no title';
	  if(!$tag)        $tag         = 'no tag';
	  if(!$description)$description = 'no description';
	  if(!$owner_uid)  $owner_uid   = 999999;
	  if(!$permission) $permission  = 1; // public
      addSid($sid,$type,$unit,$title,$tag,$description,$owner_uid,$permission);
      $do = 'list_sid';
  }
}
//----------------------------------------------------------------------
if($action == 'updSidForm')
{
   $sid         = $_POST['f_sid'];
   $type        = $_POST['f_type'];
   $unit        = $_POST['f_unit'];
   $title       = $_POST['f_title'];
   $tag         = $_POST['f_tag'];
   $description = $_POST['f_description'];
   $owner_uid   = $_POST['f_owner_uid'];
   $permission  = $_POST['f_permission'];
   updateSid($f_id,$sid,$type,$unit,$title,$tag,$description,$owner_uid,$permission);     
   $do = 'list_sid';
}
//----------------------------------------------------------------------
if($action == 'addUserForm')
{
   $name  = $_POST['f_username'];
   $pswd  = $_POST['f_password'];
   $uid   = $_POST['f_uid']; 
   addUser($name,$pswd,$uid);
   $do = 'list_user';
}
//----------------------------------------------------------------------
if($action == 'updUserForm')
{
   $name  = $_POST['f_username'];
   $pswd  = $_POST['f_password'];
   $uid   = $_POST['f_uid'];
   updateUser($f_id,$name,$pswd,$uid);
   $do = 'list_user';
}
//----------------------------------------------------------------------
if($action == 'addAppForm')
{
   $appid     = $_POST['f_appid'];
   $title     = $_POST['f_title'];
   $ownerid   = $_POST['f_ownerid'];
   $charttype = $_POST['f_charttype'];
   $shared    = $_POST['f_shared'];
   addApp($appid,$title,$ownerid,$charttype,$shared);
   $do = 'list_app';
}
//----------------------------------------------------------------------
if($action == 'updAppForm')
{
   $appid     = $_POST['f_appid'];
   $title     = $_POST['f_title'];
   $ownerid   = $_POST['f_ownerid'];
   $charttype = $_POST['f_charttype'];
   $shared    = $_POST['f_shared'];
   updateApp($f_id,$title,$ownerid,$charttype,$shared);       
   $do = 'list_app';
}
//----------------------------------------------------------------------
if($action == 'addValueForm')
{
   $sid = $_POST['f_sid'];
   $value = $_POST['f_value'];
   echo("sid=$sid<br>");
   echo("value=$value<br>");
   $table =  SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid;
   echo("table=$table<br>");
   //insertRow($tableName,$columns,$values)
   $valueArray  = array($value);
   $columnArray = array(SXN_COLLECTOR_DATA_COLUMN_VALUE);
   if($sid && $value) $g_dbM2->insertRow($table,$columnArray,$valueArray);
      else
   echo("No action!");
}
//----------------------------------------------------------------------
if($action == 'addOrderForm')
{
   $sid   = $_POST['f_sid'];
   if($g_dbM1->checkIfValueExists(SXN_ADMIN_TABLE_STREAMS,SXN_ADMIN_STREAMS_COLUMN_SID,$sid) == true)
   {
    $command    = $_POST['f_command'];
    $parameters = $_POST['f_parameters'];
    $order = $command.' '.$parameters;
    echo("sid=$sid<br>");
    echo("order=$order<br>");
    $valueArray  = array($sid,$order,'new');
    $columnArray = array(SXN_CONTROL_COMMANDS_COLUMN_SID,
                        SXN_CONTROL_COMMANDS_COLUMN_COMMAND,
                        SXN_CONTROL_COMMANDS_COLUMN_STATUS);
    if($sid) $g_dbM3->insertRow(SXN_CONTROL_TABLE_COMMANDS,$columnArray,$valueArray);
      else
    echo("No action!");
   }
   else
      echo("SID does not exists<br>");
      
  $do = 'list_order';
}
//----------------------------------------------------------------------
if($action == 'addDatatypeForm')
{
   $name = $_POST['f_name'];
   $unit = $_POST['f_unit'];
   
   if($unit && $name) addDataType($name,$unit);
      else
   echo("No action!");
   
     $do = 'list_dt';
}
//----------------------------------------------------------------------
if($action == 'updDatatypeForm')
{
   //$data_id   = $_POST['f_data_id'];
   $name = $_POST['f_name'];
   $unit = $_POST['f_unit'];
   updateDataType($f_id,$name,$unit); 
   $do = 'list_dt';
}

//----------------------------------------------------------------------
if($action == 'addCommandtypeForm')
{
   $name     = $_POST['f_name'];
   $description = $_POST['f_description'];
   addCommandType($name,$description);  
   $do = 'list_command_type';
}
//----------------------------------------------------------------------
if($action == 'updCommandtypeForm')
{
   $name        = $_POST['f_name'];
   $description = $_POST['f_description'];
   updateCommandType($f_id,$name,$description); 
   $do = 'list_command_type';
}
//----------------------------------------------------------------------
if($action == 'import_data')
{
   $sid = 999;
   $importFile = uploadFile();
   sscanf($importFile,"/var/www/html/nabton/nabtonServer/backer/data-%d-%d-%d-%d.nbc",$sid,$yy,$mm,$dd);
   if($sid == 999)
    {
        echo("Unable to decode nbc datafile: $importfile<br>");
    }
   if($importFile != "error" || $sid == 999)
   {
       echo("<br>Importing $importFile to database<br>");
       $in = fopen($importFile, "r") or die("can't open file r: $importFile");
       while (!feof($in)) 
       {
         $row = fgets($in);
         //echo("$row<br/>");
         sscanf($row,"%d:%d:%d %f %f",$hour,$min,$sec,$data,$delay);
         //echo("$sid $yy-$mm-$dd $hour:$min:$sec,$data,$delay<br>");
         $timestamp = date('Y-m-d G:i:s', mktime($hour, $min, $sec, $mm, $dd, $yy));
         //echo("$sid $data $timestamp<br>");
         if ($g_dbM2)
		 {
			$tableName = SXN_COLLECTOR_TABLE_DATA_PREFIX . $sid;
			//$doesTableExist = $this->mysqli_->query("SELECT 1 FROM $tableName");
            //$doesTableExist = $g_dbM2->selectAllFromTable($tableName, $conditions);
            $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS, SXN_ADMIN_STREAMS_COLUMN_SID." = $sid");
            $doesSidExist = $g_dbM1->retrieveNumberOfResults();
            //$g_dbM2->checkIfValueExists(SXN_ADMIN_TABLE_USERS,SXN_ADMIN_USERS_COLUMN_NAME,$name) == false
			if ($doesSidExist)
			{
				//echo "Sid is ok! data=$data time=$timestamp \n";
                $valueArray  = array($data,
                           $timestamp);

                 $columnArray = array(SXN_COLLECTOR_DATA_COLUMN_VALUE,
                               SXN_GENERAL_COLUMN_TIMESTAMP); 
                $g_dbM2->insertRow($tableName, $columnArray , $valueArray);
				//return true;
			}
			else
			{
				echo "Sid does not exists! ($doesSidExist)\n";
			}
		}
		else
		{
			echo "-Database is not open. Data not added!\n";
		}
       }
       fclose($in);
   }
}
//----------------------------------------------------------------------
// End POST
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//-------------------------------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//-------------------------------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//-------------------------------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//-------------------------------------------------------------------
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//-------------------------------------------------------------------
//----------------------------------------------------------------------
// Start BODY
//----------------------------------------------------------------------
echo("<body>");
echo("<div class=\"top\">");

echo("<div class=\"top_left\">");
//echo("<h1><a href=\"#\">Nabton Admin 2015-02-11</a></h1>");
echo("<h1><a href=\"index.php\" target=\"_blank\">SXNDATA 2015-11-16</a></h1>");
echo("</div>");

//================================================================
// Application
echo("<div class=\"top_middle\">");
echo("<a href=\"sxn_config.php?do=import\">Import data from NBC logfile</a> <br>");
if($do=="import")
{
 echo("
 <form action=\"sxn_config.php\" method=\"post\" enctype=\"multipart/form-data\">
    Select NBC logfile to upload and import to database:
    <input name=\"f_action\" type=\"hidden\" value=\"import_data\" />
    <input type=\"file\" name=\"import_file\" value=\"\">
    <input type=\"submit\" value=\"Import Data to DB\" name=\"submit_file\">
 </form> 
");
}

echo("<a href=\"sxn_config.php?do=add_app\">Add application</a> <br>");
echo("<a href=\"sxn_config.php?do=list_app\">List applications</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=del_user\">Delete user</a> <br>");
echo("<br>");
if($do == 'upd_app')
{
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_APPLICATIONS, SXN_GENERAL_COLUMN_ID." = $s_id");
  while($data = $g_dbM1->retrieveResult())
  {
    $id        = $data[SXN_GENERAL_COLUMN_ID];
    $appid     = $data[SXN_ADMIN_APPLICATIONS_COLUMN_APPID];
    $title     = $data[SXN_ADMIN_APPLICATIONS_COLUMN_TITLE];
    $ownerid   = $data[SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID];
    $shared    = $data[SXN_ADMIN_APPLICATIONS_COLUMN_SHARED];
    $charttype = $data[SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE];
  }
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"updAppForm\" />
            <input name=\"f_id\" type=\"hidden\" value=\"$s_id\" />
            <tr><td>App Id:</td><td> <input name=\"f_appid\" type=\"text\" size=\"5\" value=\"$appid\" /></td></tr>
            <tr><td>Title:</td><td> <input name=\"f_title\" type=\"text\" size=\"15\" value=\"$title\"/></td></tr>
            <tr><td>Owner Id:</td><td> <input name=\"f_ownerid\" type=\"text\" size=\"15\"  value=\"$ownerid\"/></td></tr>
            <tr><td>Shared:</td><td> <input name=\"f_shared\" type=\"text\" size=\"15\"  value=\"$shared\"/></td></tr>
            <tr><td>Chart Type:</td><td> <input name=\"f_charttype\" type=\"text\" size=\"15\"  value=\"$charttype\"/></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Update User\" /></td><td></td></tr>
        </form></table>
  ");
  $do = 'list_app';
}
if($do == 'add_app')
{
	  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addAppForm\" />
            <tr><td>App Id:</td><td> <input name=\"f_appid\" type=\"text\" size=\"5\" value=\"\" /></td></tr>
            <tr><td>Title:</td><td> <input name=\"f_title\" type=\"text\" size=\"15\" value=\"\"/></td></tr>
            <tr><td>Owner Id:</td><td> <input name=\"f_ownerid\" type=\"text\" size=\"15\"  value=\"\"/></td></tr>
            <tr><td>Shared:</td><td> <input name=\"f_shared\" type=\"text\" size=\"15\"  value=\"\"/></td></tr>
            <tr><td>Chart Type:</td><td> <input name=\"f_charttype\" type=\"text\" size=\"15\"  value=\"\"/></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Add User\" /></td><td></td></tr>
        </form></table>
        ");
 
    $do = 'list_app';
}


if($do == 'del_app')
{
   $g_dbM1->deleteRow(SXN_ADMIN_TABLE_APPLICATIONS,'id',$s_id);
   $do = 'list_app';
}

if($do == 'list_app')
{
	 echo("<table border=\"1\">");
     echo("<tr>");
     echo("<td>APP ID</td> ");
     echo("<td>TITLE</td>");
     echo("<td>OWNER ID</td>");
     echo("<td>CHART TYPE</td>");
     echo("<td>SHARED</td>");
     echo("</tr>");
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_APPLICATIONS, "");
  while($data = $g_dbM1->retrieveResult())
  {
	echo("<tr>");
	$id   = $data['id'];
    $v2 = $data[SXN_ADMIN_APPLICATIONS_COLUMN_TITLE];
    $v3 = $data[SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID];
    $v4 = $data[SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE];
    $v5 = $data[SXN_ADMIN_APPLICATIONS_COLUMN_SHARED];
    //$ts   = $data['ts'];
    echo("<td><a href=\"sxn_config.php?do=upd_app&a_id=$id\">$id</a></td>");
    echo("<td>$v2</td>");
    echo("<td>$v3</td>");
    echo("<td>$v4</td>");
    echo("<td>$v5</td>");
    echo("<td><a href=\"sxn_config.php?do=del_app&a_id=$id\">delete</a></td>");
    echo("</tr>");
  }
  echo("</table>");
}
echo("</div>");


echo("<div class=\"top_right\">");
echo("<h1><a href=\"index.php\" target=\"_blank\">SXNDATA</a></h1>");
echo("</div>");



echo("</div>");
// Database  
echo("<div class=\"middle\">");
//================================================================
echo("<div class=\"middle_left\">");
echo("<a href=\"sxn_config.php?do=add_db\">Add database</a> <br>");
echo("<a href=\"sxn_config.php?do=init_db\">Init database</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=upd_db\">Update database</a> <br>");
//echo("<a href=\"sxn_config.php?do=list_db\">List databases</a> <br>");
echo("<a href=\"sxn_config.php?do=del_db\">Delete database</a> Warning!<br>");
echo("<br>");
if($do == 'add_db')
{
  createNabtonDatabases();
}

//**********************************************************************
//**********************************************************************
//**********************************************************************
if($do == 'init_db')
{
  addUser('admin','amazon',1);  
  addUser('benny','amazon',2);
   
  addDataType('Temperature','Celcius');
  addDataType('Body Weight','Kilogram');
  addDataType('Electric Power','Watt'); 
  
  addCommandType('SXN_DEVICE_DELAY','Delay(ms) device gwy');   

  addCommandType('SXN_STEPPER_CTRL','Control of stepper motor');   

  addSid(901,"","","sxn test901","sxntest901","SXN Only test901",1,1);    
  addSid(902,"","","sxn test902","sxntest902","SXN Only test902",1,1);                               
}
//**********************************************************************
//**********************************************************************
//**********************************************************************
if($do == 'upd_db')
{
  
}
if($do == 'list_db')
{
  
  listNabtonDatabases();
}
if($do == 'del_db')
{
  
  dropNabtonDatabases();
}
echo("</div>");
//================================================================
// Users
echo("<div class=\"middle_middle\">");
echo("<a href=\"sxn_config.php?do=add_user\">Add user</a> <br>");
echo("<a href=\"sxn_config.php?do=list_user\">List users</a> <br>");
echo("<br>");
if($do == 'upd_user')
{
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_USERS, SXN_ADMIN_USERS_COLUMN_UID." = $s_uid");
  while($data = $g_dbM1->retrieveResult())
  {
    $id   = $data['id'];
    $uid  = $data[SXN_ADMIN_USERS_COLUMN_UID];
    $name = $data[SXN_ADMIN_USERS_COLUMN_NAME];
    $pswd = $data[SXN_ADMIN_USERS_COLUMN_PASSWORD];
  }
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"updUserForm\" />
            <input name=\"f_id\" type=\"hidden\" value=\"$id\" />
            <tr><td>User Id:</td><td> <input name=\"f_uid\" type=\"text\" size=\"5\" value=\"$uid\" /></td></tr>
            <tr><td>User Name:</td><td> <input name=\"f_username\" type=\"text\" size=\"15\" value=\"$name\"/></td></tr>
            <tr><td>Password:</td><td> <input name=\"f_password\" type=\"text\" size=\"15\"  value=\"$pswd\"/></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Update User\" /></td><td></td></tr>
        </form></table>
  ");
  $do = 'list_user';
}
if($do == 'add_user')
{
    echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addUserForm\" />
            <tr><td>User Id:</td><td><input name=\"f_uid\" type=\"text\" size=\"5\" /></td></tr>
            <tr><td>User Name:</td><td> <input name=\"f_username\" type=\"text\" size=\"20\" /></td></tr>
            <tr><td>Password:</td><td> <input name=\"f_password\" type=\"text\" size=\"20\" /></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Add User\" /></td><td></td></tr>
        </form></table>
      ");
    $do = 'list_user';
}


if($do == 'del_user')
{
   $g_dbM1->deleteRow(SXN_ADMIN_TABLE_USERS,'id',$s_id);
   $do = 'list_user';
}

if($do == 'list_user')
{
	 echo("<table border=\"1\">");
     echo("<tr>");
     echo("<td>UID</td> ");
     echo("<td>USER NAME</td>");
     echo("<td>USER PSWD</td>");
     echo("<td>TIMESTAMP</td>");
     echo("<td>Action</td>");
     echo("</tr>");
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_USERS, "");
  while($data = $g_dbM1->retrieveResult())
  {
	echo("<tr>");
	$id   = $data[SXN_GENERAL_COLUMN_ID];
    $uid  = $data[SXN_ADMIN_USERS_COLUMN_UID];
    $name = $data[SXN_ADMIN_USERS_COLUMN_NAME];
    $pswd = $data[SXN_ADMIN_USERS_COLUMN_PASSWORD];
    $ts   = $data[SXN_GENERAL_COLUMN_TIMESTAMP];
    echo("<td><a href=\"sxn_config.php?do=upd_user&uid=$uid\">$uid </a></td>");
    echo("<td>$name</td>");
    echo("<td>$pswd</td>");
    echo("<td>$ts</td>");
    echo("<td><a href=\"sxn_config.php?do=del_user&id=$id\">delete</a></td>");
    echo("</tr>");
  }
  echo("</table>");
}
echo("</div>");
//================================================================
echo("<div class=\"middle_right\">");
// Commands Types
echo("<a href=\"sxn_config.php?do=add_command_type\">Add command type</a> <br>");
echo("<a href=\"sxn_config.php?do=list_command_type\">List command types</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=del_order\">Delete command type</a> <br>");
echo("<br>");

if($do == 'add_command_type')
{
    echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addCommandtypeForm\" />
            <tr><td>Command Name:</td><td> <input name=\"f_name\" type=\"text\" size=\"50\" /></td></tr>
            <tr><td>Command Description:</td><td> <input name=\"f_description\" type=\"text\" size=\"50\" /></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Add Command Type\" /></td><td></td></tr>
        </form></table>
      ");
      $do = 'list_command_type';
}

if($do == 'del_command_type')
{
     $g_dbM1->deleteRow(SXN_ADMIN_TABLE_COMMANDTYPES,'id',$s_id);
     $do = 'list_command_type';
}

if($do == 'upd_command_type')
{
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_COMMANDTYPES, SXN_GENERAL_COLUMN_ID." = $s_id");
  while($data = $g_dbM1->retrieveResult())
  {
    $id          = $data['id'];
    $name        = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_NAME];
    $description = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION];
  }
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"updCommandtypeForm\" />
            <input name=\"f_id\" type=\"hidden\" value=\"$id\" />
            <tr><td>Command Name:</td><td> <input name=\"f_name\" type=\"text\" size=\"50\" value=\"$name\"/></td></tr>
            <tr><td>Command Description:</td><td> <input name=\"f_description\" type=\"text\" size=\"50\"  value=\"$description\"/></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Update\" /></td><td></td></tr>
        </form></table>
  ");
   $do = 'list_command_type';
}

if($do == 'list_command_type')
{
   echo("<table border=\"1\">");
   echo("<tr>");
   echo("<td>COMMAND</td> ");
   echo("<td>DESCRIPTION</td>");
   echo("<td>Action</td>");
   echo("</tr>");
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_COMMANDTYPES, "");
  while($data = $g_dbM1->retrieveResult())
  {
	 $id          = $data[SXN_GENERAL_COLUMN_ID];
     $name        = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_NAME];
     $description = $data[SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION];
     $ts          = $data[SXN_GENERAL_COLUMN_TIMESTAMP];
     echo("<tr>");
     echo("<td><a href=\"sxn_config.php?do=upd_command_type&a_id=$id\">$name</a></td>");
     echo("<td>$description</td>");
     echo("<td><a href=\"sxn_config.php?do=del_command_type&a_id=$id\">delete</a></td>");
     echo("</tr>");
  }
  echo("</table>");
}
echo("</div>");
echo("</div>");
echo("<div class=\"bottom\">");
//================================================================
echo("<div class=\"bottom_left\">");
// Orders
echo("<a href=\"sxn_config.php?do=add_order\">Add order</a> <br>");
echo("<a href=\"sxn_config.php?do=list_order\">List orders</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=del_order\">Delete order</a> <br>");
echo("<br>");

if($do == 'add_order')
{
	readCommandTypes();
    echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addOrderForm\" />
            <tr><td>SID:</td><td> <input name=\"f_sid\" type=\"text\" size=\"30\" /></td></tr> 
            <tr><td>Order:</td><td> <select name=\"f_command\">");
            for($ii=1;$ii<=count($g_command_type_name);$ii++)
            {
			   $temp1 = $g_command_type_map[$ii];
			   $temp2 = $g_command_type_name[$ii];
               echo("<option value=\"$temp1\">$temp2 </option>");
            }
            echo("</select></td></tr>  
            <tr><td>Order value:</td><td> <input name=\"f_parameters\" type=\"text\" size=\"30\" /></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Enter value\" /></td><td></td></tr>
        </form></table>
      ");
     $do = 'list_order';
}

if($do == 'del_order')
{
   $g_dbM3->deleteRow(SXN_CONTROL_TABLE_COMMANDS,'id',$s_id);
   $do = 'list_order';
}

if($do == 'list_order')
{
   echo("<table border=\"1\">");
   echo("<tr>");
   echo("<td>SID</td> ");
   echo("<td>ORDER</td>");
   echo("<td>STATUS</td>");
   echo("<td>TIMESTAMP</td>");
   echo("<td>Action</td>");
   echo("</tr>");
  $g_dbM3->selectAllFromTable(SXN_CONTROL_TABLE_COMMANDS, "");
  while($data = $g_dbM3->retrieveResult())
  {
	 $id     = $data[SXN_GENERAL_COLUMN_ID];
     $sid    = $data[SXN_CONTROL_COMMANDS_COLUMN_SID];
     $order  = $data[SXN_CONTROL_COMMANDS_COLUMN_COMMAND];
     $status = $data[SXN_CONTROL_COMMANDS_COLUMN_STATUS];
     $ts     = $data[SXN_GENERAL_COLUMN_TIMESTAMP];
     echo("<tr>");
     echo("<td>$sid</td>");
     echo("<td>$order</td>");
     echo("<td>$status</td>");
     echo("<td>$ts</td>");
     echo("<td><a href=\"sxn_config.php?do=del_order&a_id=$id\">delete</a></td>");
     echo("</tr>");
  }
  echo("</table>");
}
echo("</div>");
//================================================================
echo("<div class=\"bottom_middle\">");
// SID
echo("<a href=\"sxn_config.php?do=add_sid\">Add SID</a> <br>");
echo("<a href=\"sxn_config.php?do=list_sid\">List all SIDs</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=del_sid\">Delete SID</a> <br>");
echo("<a href=\"sxn_config.php?do=latest_sid\">Latest value SID</a> <br>");
echo("<a href=\"sxn_config.php?do=add_data_sid\">Add data SID</a> <br>");
echo("<br>");

if($do == 'add_sid')
{
  readDataTypes(); 
  echo(" <table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
              <input name=\"f_action\" type=\"hidden\" value=\"addSidForm\" />
              <tr><td>SID</td><td><input name=\"f_sid\" type=\"text\" size=\"50\" /></td></tr> 
              <tr><td>Data Type</td><td><select name=\"f_type\"> ");
              for($ii=1;$ii<=count($g_data_name);$ii++)
              {
                    echo("<option value=\"$g_data_type_map[$ii]\">$g_data_name[$ii] ($g_data_unit[$ii]) </option>");
              }
              echo(" </select></td></tr>
              <tr><td>Title</td><td>      <input name=\"f_title\"       type=\"text\" size=\"50\" /></td></tr>
              <tr><td>Tag</td><td>        <input name=\"f_tag\"         type=\"text\" size=\"50\" /></td></tr>
              <tr><td>Description</td><td><input name=\"f_description\" type=\"text\" size=\"50\" /></td></tr> 
              <tr><td>OwnerId</td><td>    <input name=\"f_owner_uid\"   type=\"text\" size=\"50\" /></td></tr>        
              <tr><td>Permission</td><td> <input name=\"f_permission\"  type=\"text\" size=\"50\" /></td></tr>          
              <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Add new SID\" /></td><td></td></tr>
          </form></table>");  
   $do = 'list_sid';       
}
if($do == 'upd_sid')
{
  readDataTypes(); 
  $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS, SXN_GENERAL_COLUMN_ID." = $s_id");
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
  $do = 'list_sid';
}

if($do == 'del_sid')
{
  $g_dbM1->deleteRow(SXN_ADMIN_TABLE_STREAMS,'id',$s_id);
  $do = 'list_sid';
}

if($do == 'list_sid')
{
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
}

if($do == 'latest_sid')
{
  echo("<div id=\"getmytime\">no data</div>");
}
if($do == 'add_data_sid')
{
  
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addValueForm\" />
            <tr><td>SID:</td><td> <input name=\"f_sid\" type=\"text\" size=\"5\" /></td></tr>
            <tr><td>Value:</td><td> <input name=\"f_value\" type=\"text\" size=\"5\" /></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Enter value\" /></td><td></td></tr>
        </form></table>
      ");
}
echo("</div>");
//================================================================
echo("<div class=\"bottom_right\">");
// Data Type
echo("<a href=\"sxn_config.php?do=add_dt\">Add Data Type</a> <br>");
echo("<a href=\"sxn_config.php?do=list_dt\">List Data Types</a> <br>");
//echo("TODO: <a href=\"sxn_config.php?do=del_dt\">Delete DataType</a> <br>");
echo("<br>");


if($do == 'add_dt')
{
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"addDatatypeForm\" />
            <tr><td>Data Name:</td><td> <input name=\"f_name\" type=\"text\" size=\"20\" /></td></tr>
            <tr><td>Data Unit:</td><td> <input name=\"f_unit\" type=\"text\" size=\"20\" /></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"New Data Type\" /></td><td></td></tr>
        </form></table>
        ");
     $do = 'list_dt';
}
if($do == 'upd_dt')
{
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_DATATYPES, SXN_GENERAL_COLUMN_ID." = $s_id");
  while($data = $g_dbM1->retrieveResult())
  {
    $id   = $data[SXN_GENERAL_COLUMN_ID];
    $name = $data[SXN_ADMIN_DATATYPES_COLUMN_NAME];
    $unit = $data[SXN_ADMIN_DATATYPES_COLUMN_UNIT];
  }
  echo("<table border=\"1\">
          <form action=\"sxn_config.php\" method=\"post\">
            <input name=\"f_action\" type=\"hidden\" value=\"updDatatypeForm\" />
            <input name=\"f_id\" type=\"hidden\" value=\"$id\" />
            <tr><td>Data Name:</td><td> <input name=\"f_name\" type=\"text\" size=\"20\" value=\"$name\"/></td></tr>
            <tr><td>Data Unit:</td><td> <input name=\"f_unit\" type=\"text\" size=\"20\"  value=\"$unit\"/></td></tr>
            <tr><td><input name=\"f_submit\" type=\"submit\" value=\"Update Data Type\" /></td><td></td></tr>
        </form></table>
  ");
    $do = 'list_dt';
}

if($do == 'del_dt')
{
   $g_dbM1->deleteRow(SXN_ADMIN_TABLE_DATATYPES,SXN_GENERAL_COLUMN_ID,$s_id);
   $do = 'list_dt';
}

if($do == 'list_dt')
{
     echo("<table border=\"1\">");
     echo("<tr>");
     echo("<td>DATA NAME</td>");
     echo("<td>DATA UNIT</td>");
     echo("<td>Action</td>");
     echo("</tr>");
   $g_dbM1->selectAllFromTable(SXN_ADMIN_TABLE_DATATYPES, "");
   while($data = $g_dbM1->retrieveResult())
   {
	 $id    = $data[SXN_GENERAL_COLUMN_ID];
     $name  = $data[SXN_ADMIN_DATATYPES_COLUMN_NAME];
     $unit  = $data[SXN_ADMIN_DATATYPES_COLUMN_UNIT];
     echo("<tr><td><a href=\"sxn_config.php?do=upd_dt&a_id=$id\">$name </a></td> ");
     echo("<td>$unit</td>");
     echo("<td><a href=\"sxn_config.php?do=del_dt&a_id=$id\">delete</a></td>");
     echo("</tr>");
   }
   echo("</table>");
}
echo("</div>");
echo("</div>");
?>

<hr>
</body>
</html>
