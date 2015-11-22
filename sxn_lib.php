<?php
require_once('sxn_definition.php');
define ("DEBUG_MODE", "off", true);
//==============================================================================
//Class DataManager
//Author: asaxen
//==============================================================================
class DataManager
{
	private $username_;
	private $password_;
	private $server_;  
	private $dbName_;  
	private $mysqli_; 
	private $isDbOpen_;

	//Result variables
	private $result_;

	public function __construct($user, $pass, $server, $dbName)
	{
		$this->username_   = $user;
		$this->password_   = $pass;
		$this->server_     = $server;
		$this->dbName_     = $dbName;
		$this->isDbOpen_   = false;

		$this->openDataBase();
	}

	public function __destruct()
	{
		$this->closeDataBase();
	}

	/*
	* Function opensDataBase
	* Desc: Opens database and sets flag isDbOpen to true if success 
	*/
	private function openDataBase()
	{
		$this->mysqli_ = new mysqli($this->server_,
									$this->username_,
									$this->password_,
									$this->dbName_);

		if ($this->mysqli_->connect_error) 
		{
		}
		else
		{
			$this->isDbOpen_ = true;
		}
	}

	/*
	* Function closeDataBase
	* Desc: Closes database 
	*/
	public function closeDataBase()
	{
		$this->mysqli_->close();
	}

	/*
	* Function selectAllFromTable
	* Desc: select all columns from table with optional $conditions 
	* 	Examples: 
	*   - No  conditions:  selecAllFromtable("SXN_users", "")
	*   - One conditions:  selecAllFromtable("SXN_users", "uid=4")
	*   - Sev. conditions: selecAllFromtable("SXN_users", "uid=4 AND SXN_username LIKE 'adam'")
	*/
	public function selectAllFromTable($tableName, $conditions)
	{
		if ($this->isDbOpen_)
		{
			$doesTableExist = $this->mysqli_->query("SELECT 1 FROM $tableName");
			if ($doesTableExist)
			{
				if($conditions == '')
				{
					$this->result_ = $this->mysqli_->query("SELECT * FROM $tableName");
				}
				else
				{
					$this->result_ = $this->mysqli_->query("SELECT * FROM $tableName WHERE $conditions");
				}	
			}
			else
			{
				if(DEBUG_MODE == 'on')echo "Table does not exists! \n";
			}
		}
	}
    
    
    
	public function selectFromQuery($query)
	{
        if ($this->isDbOpen_)
        {
            $this->result_ = $this->mysqli_->query($query);
        }
	}

	/*
	* Function retrieveResult
	* Desc: Is called to retrieve data from select funtion
	*  Example: 1. Function selecAllFromtable("SXN_users", "") called
	*           2. To retrieve all rows in result do "while($data = retrieveResult())" from your application
	*			3. Column data is stored as follows: $data['uid'], $data['SXN_username'] ... etc
	* Returns: $data containing one row from the results of the query. 
	* Note: If no $data exists function will return "false"
	*/
	public function retrieveResult()
	{
		if($data = $this->result_->fetch_array())
		{
			return $data;
		}
		else
		{
			return false;
		}
	}

	/*
	* Function retrieveNumberOfResult
	* Desc: Is called after Select query
	* Returns: $number of rows in result  
	* Note: If no rows exist returns 0
	*/
	public function retrieveNumberOfResults()
	{
		return $data = $this->result_->num_rows;
	}

	/*
	* Function checkIfValueExists
	* Desc: Check if value exists in column $columnName in table $tableName
	* Return: true or false
	*/
	public function checkIfValueExists($tableName,$columnName,$value)
	{
		if ($this->isDbOpen_)
		{
			$res = $this->mysqli_->query("SELECT $columnName FROM $tableName WHERE $columnName = '$value'");
			if ($res->num_rows)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/*
	* Function deleteRow
	* Desc: Delete row in table
	* Return: none
	*/
	public function deleteRow($tableName,$column,$value)
	{
		if ($this->isDbOpen_)
		{
		    $sqlQuery = "DELETE FROM $tableName WHERE $column = '$value'";
		    if(DEBUG_MODE == 'on')echo("$sqlQuery");
		    $res = $this->mysqli_->query($sqlQuery);
		}
	}

		
	/*
	* Function insertRow
	* Desc: Insert row in table
	* Return: none
	*/
	public function insertRow($tableName,$columns,$values)
	{
		if ($this->isDbOpen_)
		{
			//TODO check if $tableName exists
			if(count($columns) == count($values))
			{
				$sqlQuery = "INSERT INTO $tableName ";
				$columnsPart = "";
				$valuesPart  = "";	
				
				for($ii=0;$ii<count($columns)-1;$ii++)
				{
					$columnsPart = $columnsPart."$columns[$ii],";
					$valuesPart  = $valuesPart."'$values[$ii]',";
				}

				$lastIndex = count($columns)-1;
				$columnsPart = $columnsPart."$columns[$lastIndex]";
				$valuesPart  = $valuesPart."'$values[$lastIndex]'";

				//Build complete query
				$sqlQuery = $sqlQuery.'('.$columnsPart.') VALUES ('.$valuesPart.')';				
				if(DEBUG_MODE == 'on')echo("$sqlQuery");
				$res = $this->mysqli_->query($sqlQuery);
			}
		}
	}

    /*
	* Function updateRow
	* Desc: update row/rows in table
	* Return: none
	*/
	public function updateRow($tableName,$columns,$values,$conditions)
	{
		if ($this->isDbOpen_)
		{
			//TODO check if $tableName exists
			if(count($columns) == count($values))
			{
				$sqlQuery = "UPDATE $tableName SET ";

				for($ii=0;$ii<count($columns)-1;$ii++)
				{
					$sqlQuery    = $sqlQuery."$columns[$ii]='$values[$ii]',";
				}

				$lastIndex = count($columns)-1;
				$sqlQuery    = $sqlQuery."$columns[$ii]='$values[$ii]' ";
			    $sqlQuery    = $sqlQuery."WHERE $conditions";
				
				if(DEBUG_MODE == 'on')echo("$sqlQuery");
				$res = $this->mysqli_->query($sqlQuery);
			}
		}
	}
    
    /*
	* Function selectDistrinctDate
	* Desc: Gets distinct dates from timestamp of table
	* Return: none
	*/
	public function selectDistinctDates($table, $date_column)
	{
		if ($this->isDbOpen_)
		{
            $this->result_ = $this->mysqli_->query("SELECT DISTINCT DATE($date_column) AS dates FROM $table");	
		}
	}
 
    
       /*
	* Function selectMaxValue
	* Desc: Gets max value from of table
	* Return: none
	*/
	public function selectMaxValue($table,$column)
	{
		if ($this->isDbOpen_)
		{
            $this->result_ = $this->mysqli_->query("SELECT MAX($column) FROM $table");	
		}
	}
    
	/*
	* Function createSidTable
	* Desc: Create SID table
	* Return: none
	*/
	public function createSidTable($sid)
	{
		if ($this->isDbOpen_)
		{
			//TODO check if $tableName exists
			if($sid > 0)
			{
				echo "adding table $sid";
				$table = SXN_COLLECTOR_TABLE_DATA_PREFIX.$sid;
				$sqlQuery = sprintf("CREATE TABLE $table (
                             id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                             %s DOUBLE,
                             ts TIMESTAMP
                            )",
                            SXN_COLLECTOR_DATA_COLUMN_VALUE);
				$res = $this->mysqli_->query($sqlQuery);
			}
		}
	}

}


?>
