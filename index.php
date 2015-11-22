<?php
require_once('sxn_definition.php');
require_once('sxn_lib.php');

//define ("DEBUG_MODE", "off", true);

//==============================================================================
//Class DataManager
//==============================================================================
class DataCollector
{

	private $username_;
	private $password_;
	private $server_;  
	private $dbName_;  
	private $mysqli_; 
	private $isDbOpen_;

	public function __construct($user, $pass, $server, $dbName)
	{
		//echo "-SXN_DATABASE_COLLECTOR created \n";
		$this->username_   = $user;
		$this->password_   = $pass;
		$this->server_     = $server;
		$this->dbName_     = $dbName;
		$this->isDbOpen_   = false;
	}

	public function __destruct()
	{
		$this->closeDataBase();
		if(DEBUG_MODE == 'on')echo "-DataManager destroyed \n";
	}

	public function openDataBase()
	{
		//echo "-Opening database\n";

		$this->mysqli_ = new mysqli($this->server_,
									$this->username_,
									$this->password_,
									$this->dbName_);
		if ($this->mysqli_->connect_error) 
		{
			if(DEBUG_MODE == 'on')echo "-Problem opening database\n";
		}
		else
		{
			$this->isDbOpen_ = true;
		}
	}

	public function closeDataBase()
	{
		if(DEBUG_MODE == 'on')echo "-Closeing database\n ";
		$this->mysqli_->close();
	}

	public function insertData($sid, $data)
	{	
		if ($this->isDbOpen_)
		{
			$tableName = SXN_COLLECTOR_TABLE_DATA_PREFIX . $sid;
			$doesTableExist = $this->mysqli_->query("SELECT 1 FROM $tableName");

			if ($doesTableExist)
			{
				if(DEBUG_MODE == 'on')echo "Sid is ok! \n";
			    $param = SXN_COLLECTOR_DATA_COLUMN_VALUE;
				$this->mysqli_->query("INSERT INTO $tableName ($param) VALUES ($data)");

				return true;
			}
			else
			{
				if(DEBUG_MODE == 'on')echo "Sid does not exists! \n";
				
			}
		}
		else
		{
			if(DEBUG_MODE == 'on')echo "-Database is not open. Data not added!\n";

		}
		return false;
	}


}


function getControlMessage($sid) {
 
	$dbM = new DataManager   (SXN_USER, SXN_PASSWORD, "localhost", SXN_DATABASE_CONTROL);
	$dbM->selectAllFromTable(SXN_CONTROL_TABLE_COMMANDS,
							 SXN_CONTROL_COMMANDS_COLUMN_SID."="."$sid AND ".
							 SXN_CONTROL_COMMANDS_COLUMN_STATUS."="."'new'"); //TODO add enum table for new, etc..

	$numRes = $dbM->retrieveNumberOfResults();

	if($numRes>0)
	{
		while($data = $dbM->retrieveResult())
		{
		    echo $data[SXN_CONTROL_COMMANDS_COLUMN_COMMAND]."\n";
		}

		//Update status to "executed"
		$columns = array(SXN_CONTROL_COMMANDS_COLUMN_STATUS);
		$values  = array("executed");
		$dbM->updateRow(SXN_CONTROL_TABLE_COMMANDS, $columns, $values, 
						SXN_CONTROL_COMMANDS_COLUMN_SID."="."$sid AND "
						.SXN_CONTROL_COMMANDS_COLUMN_STATUS."="."'new'");
	}
}
function getLatestValue($sid) {
   echo("hej2");
	$dbM = new DataManager(SXN_USER, SXN_PASSWORD, "localhost", SXN_DATABASE_COLLECTOR);
    $stemp = SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid;
    echo $stemp;
	$dbM->selectMaxValue($stemp,SXN_GENERAL_COLUMN_ID);
	$numRes = $dbM->retrieveNumberOfResults();
  echo("hej3 $numRes ***");
	if($numRes>0)
	{
		while($data = $dbM->retrieveResult())
		{
            $stemp = SXN_COLLECTOR_DATA_COLUMN_VALUE;
		    echo $data[$stemp]." ??\n";
		}
	}
}

//==============================================================================
// Main program
//==============================================================================

if(isset($_GET['mid']) && isset($_GET['nsid']))
{

	//Data from request
	$mid  = $_GET["mid"];
    $nsid  = $_GET["nsid"];
    if($nsid > 9 || $nsid < 1) die;
    
    if($nsid > 0) {$msid[1]  = $_GET["sid1"];$dat[1]  = $_GET["dat1"];}
    if($nsid > 1) {$msid[2]  = $_GET["sid2"];$dat[2]  = $_GET["dat2"];}
    if($nsid > 2) {$msid[3]  = $_GET["sid3"];$dat[3]  = $_GET["dat3"];}
    if($nsid > 3) {$msid[4]  = $_GET["sid4"];$dat[4]  = $_GET["dat4"];}
    if($nsid > 4) {$msid[5]  = $_GET["sid5"];$dat[5]  = $_GET["dat5"];}
    if($nsid > 5) {$msid[6]  = $_GET["sid6"];$dat[6]  = $_GET["dat6"];}
    if($nsid > 6) {$msid[7]  = $_GET["sid7"];$dat[7]  = $_GET["dat7"];}
    if($nsid > 7) {$msid[8]  = $_GET["sid8"];$dat[8]  = $_GET["dat8"];}
    if($nsid > 8) {$msid[9]  = $_GET["sid9"];$dat[9]  = $_GET["dat9"];}
    
    
    for($ii=1;$ii<=$nsid;$ii++)
    {
	   $sid = $msid[$ii];
        $dd = $dat[$ii];
	   $dbM = new DataManager   (SXN_USER, SXN_PASSWORD, "localhost", SXN_DATABASE_ADMIN);
	   $dbM->selectAllFromTable(SXN_ADMIN_TABLE_STREAMS,
							 SXN_ADMIN_STREAMS_COLUMN_SID."=".$sid); 

	   $numRes = $dbM->retrieveNumberOfResults();
	   if($numRes == 1)
	   {
		  if ($data = $dbM->retrieveResult())
		  {
			 if($mid == SXN_DATA)
			 {
		    	   $dbC = new DataCollector (SXN_USER, SXN_PASSWORD, "localhost", SXN_DATABASE_COLLECTOR);
				   $dbC->openDataBase();
				   $success = $dbC->insertData($sid, $dd);
	               echo "$sid";
                   getControlMessage($sid);
		      }
              
             if($mid == SXN_LATEST)
             {
                 echo("hej");
                 getLatestValue($sid);
             }
             if($mid == SXN_MAILBOX)
             { 
                 getControlMessage($sid);
             }
		     
		  }
	   }
    }
}
else
{
	if(DEBUG_MODE == 'on')echo "- Not a valid request, die \n";
	die();
}

?>
