<?php

/*
 * Singleton - MSSQL Database Connection
 */

class mssql
{
	private $databasePool = array();
	private $connection;

	
	public static function getInstance()
	{
		static $instance;
		
		//page::addDebug("Request connection for " . mssql_USERNAME . "@" . mssql_PASSWORD, __FILE__, __LINE__);
		
		if (mssql_USERNAME == 'root')
		{
			die("root mssql user used, this is a security risk");
		}

		if (!isset($instance))
		{
			//page::addDebug("Make connection", __FILE__, __LINE__);
			
            $c = __CLASS__;
            
            $GLOBALS['sql_debug'] = '';

            $instance = new $c;
           	$instance->makeConnection();
        }

        return $instance;
	}
	
	public function initiate()
	{
		//
	}

	public function makeConnection()
	{
		$start = common::getTime();
		
		//echo "trying...<br>";
		
		//die ("Connect to mssql as " . mssql_USERNAME . " " . mssql_PASSWORD);
		
		$server = 'SCAPASQL002\MSSQLSERVER';
		
		$this->connection = mssql_connect($server, 'sa', 'aoluk123');
		
		//echo "connected<br>";
		
		common::addDebug("Connect to mssql as " . 'sa' , __FILE__, __LINE__);

		if (!$this->connection)
		{
			common::error("A connection to the database server could not be established", "", __FILE__, __LINE__);
		}
		
		$timeTaken = common::getTime() - $start;
		
		common::addDebug("mssql connect time " . $timeTaken, __FILE__, __LINE__);
	
		
		$GLOBALS['sql_debug'] .= "Time to connect to mssql database: ". $timeTaken . "\n\n";
	}

	
	public function execute($query, $dieOnError=true)
	{
		//var_dump($this->connection);
		//die ("EXECUTE as " . mssql_USERNAME . " " . mssql_PASSWORD);
		
		$start = common::getTime();
		$dataset = mssql_query($query, $this->connection);
		$GLOBALS['sql_debug'] .= "Time taken for mssql query: ". (common::getTime() - $start) . "\n Query: $query\n\n";

		if (!$dataset)
		{
			if (mssql_affected_rows() > 0)
			{
				return true;
			}
			else 
			{
				common::error(mssql_error()." Database query failed $query", $query, __FILE__, __LINE__);
				//return false;
			}
			//printf("doh %d", mssql_affected_rows());
			////die ();
			//return mssql_affected_rows($this->connection);
			//return false;
		}
		

		return $dataset;
	}


	function selectDatabase($database)
	{
		mssql_select_db($database, $this->connection);

		return $this;
	}

	public function getConnection()
	{
		return $this->connection;
	}

}


class query
{
	private $query;
	
	function __construct($query)
	{
		$this->query = addslashes($query);
	}
	
	public function execute()
	{
		return $this->query;
	}
}

?>