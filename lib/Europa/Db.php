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
	
	/**
	 * Contains the configuration for the current instance.
	 * 
	 * @var array
	 */
	protected $config = array();
		
	/**
	 * Contains the query log for the current db connection.
	 * 
	 * @var array
	 */
	protected $log = array();
	
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
		
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Europa_Db_Statement'));
	}
	
	/**
	 * A shortcut for instantiating a Europa_Db_Select statement and returning
	 * it. Europa_Db and columns parameters are automatically passed.
	 * 
	 * @param array $columns A set of columns to select.
	 * @return Europa_Db_Select
	 */
	public function select($columns = '*')
	{
		$select = new Europa_Db_Select($this);
		
		return $select->columns($columns);
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