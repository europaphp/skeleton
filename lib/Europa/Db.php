<?php

/**
 * @package    Europa
 * @subpackage Db
 */

/**
 * Extends PHP's PDO library providing database abstraction and access to all public
 * methods/properties in the PDO library. Provides an easy method for connecting
 * to a PDO driver and querying the database in fewer calls.
 */
class Europa_Db extends PDO
{
	static public
		/**
		 * The default configuration.
		 * 
		 * @var array
		 */
		$defaultConfig = array(
			'driver'        => 'mysql',
			'driverOptions' => array(),
			'host'          => 'localhost',
			'database'      => null,
			'username'      => 'root',
			'password'      => null,
			'log'           => true
		);
	
	protected
		/**
		 * Contains the configuration for the current instance.
		 * 
		 * @var array
		 */
		$config = array(),
		
		/**
		 * Contains the query log for the current db connection.
		 * 
		 * @var array
		 */
		$log = array();
	
	/**
	 * Instantiates a new database connection from the given configuration options.
	 * 
	 * @param array $config The configuration to use for the instance.
	 * @return Europa_Db
	 */
	public function __construct($config = null)
	{
		// merge the configuration
		$this->config = array_merge(self::$defaultConfig, (array) $config);
		
		// create a PDO DSN
		$dsn = $this->config['driver'] . ':'
		     . 'host=' . $this->config['host'] . ';'
		     . 'dbname=' . $this->config['database'];
		
		// construct the parent PDO
		parent::__construct(
			$dsn,
			$this->config['username'],
			$this->config['password'],
			$this->config['driverOptions']
		);
	}
	
	/**
	 * Similar to PDO->query() in that it auto-executes the query, but Europa_Db->query()
	 * will use the passed $params and prepare and execute the statement for you.
	 * 
	 * Everything that goes through Europa_Db->query() gets logged and can be retrieved
	 * using Europa_Db->getLog(). Logs are in chronological.
	 * 
	 * @param string $query The query to use.
	 * @param mixed $params The parameters, if any, to use for the prepared statement.
	 * @return mixed Returns PDOStatement result on success or false on failure.
	 */
	public function query($query, $params = array())
	{
		// allow a Europa_Db_Select instance
		if ($query instanceof Europa_Db_Select) {
			$params = $query->getParams();
			$query  = (string) $query;
		}
		
		// prepare the statement, returning a PDOStatement
		$query = parent::prepare($query, array(
			PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
		));
		
		// if logging, set the query start time
		if ($this->config['log']) {
			$startTime = microtime();
		}
		
		// execute the PDOStatement and return it on success
		$res = $query->execute($params);
		
		// if logging, record query details
		if ($this->config['log']) {
			$endTime = microtime() - $startTime;
			
			$this->log[] = array(
				'database' => $this->config['database'],
				'query'    => $query->queryString,
				'params'   => $params,
				'time'     => $endTime,
				'success'  => $res,
				'error'    => $res ? false : $query->errorInfo()
			);
		}
		
		// return if successful
		if ($res) {
			return $query;
		}
		
		// make sure it's closed if it's not
		$query->closeCursor();
		
		// if the execution failed, return false
		return false;
	}
	
	/**
	 * Fetches a single row, reduces it to a single array and returns it on success. 
	 * Returns false on failure.
	 * 
	 * @param string $query  The query to execute.
	 * @param mixed $params The parameters to use in the prepared statement.
	 * @return Mixed Array on success. False on failure.
	 */
	public function fetchOne($query, $params = null)
	{
		if ($res = $this->fetchAll($query, $params)) {
			return $res[0];
		}
		
		return false;
	}
	
	/**
	 * Fetches multiple rows and returns a Europa_Db_RecordSet on success.
	 * Returns false on failure.
	 * 
	 * @param string $query  The query to execute.
	 * @param mixed $params The parameters to use in the prepared statement.
	 * @return Mixed Array on success. False on failure.
	 */
	public function fetchAll($query, $params = null)
	{
		if ($stmt = $this->query($query, $params)) {
			return new Europa_Db_RecordSet($stmt);
		}
		
		return false;
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Select statement and returning
	 * it. Europa_Db and columns parameters are authomatically passed.
	 * 
	 * @param array $columns A set of columns to select.
	 * @return Europa_Db_Select
	 */
	public function select($columns = array())
	{
		return new Europa_Db_Select($this, $columns);
	}
	
	/**
	 * Returns the query log as an array.
	 * 
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	}
}