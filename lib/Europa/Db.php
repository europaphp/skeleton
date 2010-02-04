<?php

/**
 * @package    Europa
 * @subpackage Db
 */

/**
 * Extends PHP's PDO library prviding database abstraction and access to all public
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
			'translate'     => false,
			'log'           => true
		),
		
		/**
		 * Contains the query log for the current db connection.
		 * 
		 * @var array
		 */
		$log = array();
	
	protected
		/**
		 * Contains the configuration for the current instance.
		 * 
		 * @var array
		 */
		$config = array();
	
	
	
	/**
	 * Instantiates a new database connection from the given configuration options.
	 * 
	 * @param array $config The configuraiton to use for the instance.
	 * 
	 * @return Europa_Db
	 */
	public function __construct($config = null)
	{
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
	 * Wraps PDO::query() to implement statement translation and auto-execution.
	 * 
	 * @param string $query  The query to use.
	 * @param mixed  $params The parameters to use for the prepared statement. None if not prepared.
	 * 
	 * @return mixed Returns PDOStatement result on success or false on failure.
	 */
	public function query($query, $params = array())
	{
		// retrieve the params from the statement
		// if a Europa_Db_Statement instance is passed use the params set on it
		// unless overridden with $params
		if ($query instanceof Europa_Db_Statement) {
			// passed in parameters will override bound parameters
			$params = $params ? $params : $query->getParams();
			
			// convert the query to a string
			$query  = (string) $query;
		}
		
		// prepare the statement, returning a PDOStatement
		$query = parent::prepare($this->translate($query));
		
		// if logging, set the query start time
		if ($this->config['log']) {
			$startTime = microtime();
		}
		
		// execute the PDOStatement and return it on success
		$res = $query->execute($params);
		
		// if logging, record query details
		if ($this->config['log']) {
			$endTime = microtime() - $startTime;
			
			self::$log[] = array(
				'database' => $this->config['database'],
				'query'    => $query->queryString,
				'params'   => $params,
				'time'     => $endTime,
				'success'  => $res
			);
		}
		
		// return if successful
		if ($res) {
			return $query;
		}
		
		$query->closeCursor();
		
		// if the execution failed, return false
		return false;
	}
	
	/**
	 * Fetches a single row, reduces it to a single array and returns it on success. Returns false on failure.
	 * 
	 * @param string $query  The query to execute.
	 * @param mixed  $params The parameters to use in the prepared statement.
	 * 
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
	 * Fetches mutlitple rows and returns a multi-dimensional array on success. Returns false on failure.
	 * 
	 * @param string $query  The query to execute.
	 * @param mixed  $params The parameters to use in the prepared statement.
	 * 
	 * @return Mixed Array on success. False on failure.
	 */
	public function fetchAll($query, $params = null)
	{
		if ($stmt = $this->query($query, $params)) {
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$stmt->closeCursor();
			
			return $results;
		}
		
		return false;
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Statement and then calling the select() method.
	 * 
	 * @param string|array $columns The columns to select from. Can be a string or array. Multiple columns must be specified in an array.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function select($columns = '*')
	{
		$stmt = new Europa_Db_Statement;
		
		return $stmt->select($columns);
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Statement and then calling the insert() method.
	 * 
	 * @param array $keyVals The key value pairs of $columnName => $columnValue to insert.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function insert($keyVals)
	{
		$stmt = new Europa_Db_Statement;
		
		return $stmt->insert($keyVals);
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Statement and then calling the update() method.
	 * 
	 * @param array $keyVals The key value pairs of $columnName => $columnValue to update.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function update($keyVals)
	{
		$stmt = new Europa_Db_Statement;
		
		return $stmt->update($keyVals);
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Statement and then calling the delete() method.
	 * 
	 * @return Europa_Db_Statement
	 */
	public function delete()
	{
		$stmt = new Europa_Db_Statement;
		
		return $stmt->delete();
	}
	
	/**
	 * Instantiates the appropriate translation class for the specified driver. If translation is turned
	 * off, nothing is done and the original statement is returned.
	 * 
	 * @param string $query The query to translate.
	 * 
	 * @return string
	 */
	public function translate($query)
	{
		// if we are not translating, then just return the query
		if (!$this->config['translate']) {
			return $query;
		}
		
		// so we can camelcase
		$driver = new Europa_String($this->config['driver']);
		
		// instantiate the translation object
		$tranny = 'Europa_Db_Translator_' . (string) $driver->camelCase(true);
		
		// translate and return
		return (string) new $tranny($query);
	}
}