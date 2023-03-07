<?php

/*
 * Singleton
 */

class mysqlExt
{
	private $databasePool = array();
	private $connection;


	public static function getInstance()
	{
		static $instance;

		//page::addDebug("Request connection for " . MYSQL_USERNAME . "@" . MYSQL_PASSWORD, __FILE__, __LINE__);

		if (MYSQL_USERNAME == 'root')
		{
			die("root MYSQL user used, this is a security risk");
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

		//die("Connect to mysqlExt as " . MYSQL_USERNAME_EXT . " " . MYSQL_PASSWORD_EXT);

		// Connect to the external database
		//$this->connection = mysql_connect("213.165.84.12", MYSQL_USERNAME_EXT, MYSQL_PASSWORD_EXT);
		$this->connection = mysql_connect("213.171.222.208", MYSQL_USERNAME_EXT, MYSQL_PASSWORD_EXT);

		//echo "connected<br>";

		common::addDebug("Connect to MYSQLEXT as " . MYSQL_USERNAME_EXT , __FILE__, __LINE__);


		if (!mysql_ping($this->connection)) // Reconnect if no connection ...
		{
		   mysql_close($this->connection);
		   //$this->connection = mysql_connect("213.165.84.12", MYSQL_USERNAME_EXT, MYSQL_PASSWORD_EXT);
		}


		if (!$this->connection) // Otherwise if no connection after 2nd try fail ...
		{
			//common::error("A connection to the database server could not be established", "", __FILE__, __LINE__);
			die("A connection to the server (Extranet) could not be established. Please click back and try again");
		}

		$timeTaken = common::getTime() - $start;

		common::addDebug("MYSQLEXT connect time " . $timeTaken, __FILE__, __LINE__);


		$GLOBALS['sql_debug'] .= "Time to connect to mysqlExt database: ". $timeTaken . "\n\n";
	}


	public function execute($query, $dieOnError=true)
	{
		//var_dump($this->connection);
		//die ("EXECUTE as " . MYSQL_USERNAME . " " . MYSQL_PASSWORD);

		$start = common::getTime();
		$dataset = mysql_query($query, $this->connection);
		$GLOBALS['sql_debug'] .= "Time taken for MYSQL query: ". (common::getTime() - $start) . "\n Query: $query\n\n";

		if (!$dataset)
		{
			if (mysql_affected_rows() > 0)
			{
				return true;
			}
			else
			{
				common::error(mysql_error()." Database query failed $query", $query, __FILE__, __LINE__);
				//return false;
			}
			//printf("doh %d", mysql_affected_rows());
			////die ();
			//return mysql_affected_rows($this->connection);
			//return false;
		}


		return $dataset;
	}


	function selectDatabase($database)
	{
		$database = "scapa_external"; // override all databases to one default.

		mysql_select_db($database, $this->connection);

		return $this;
	}

	public function getConnection()
	{
		return $this->connection;
	}

}


class queryExt
{
	private $query;

	function __construct($query)
	{
		$this->query = mysql_escape_string($query);
	}

	public function execute()
	{
		return $this->query;
	}
}

?>