<?php

/**
 * The MongoDB connection class. Allows management and assigning
 * of mutiple connections as well as default connections.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Mongo_Connection extends Mongo
{
    /**
     * The connections bound to the manager.
     * 
     * @var array
     */
    private static $_connections = array();
    
    /**
     * Contains the name of the default connection.
     * 
     * @var string
     */
    private static $_defaultName = 'default';
    
    /**
     * Contains the default DSN string.
     * 
     * @var string
     */
    private static $_defaultDsn = 'mongodb://localhost:27017';
    
    /**
     * Contains the default options.
     * 
     * @var array
     */
    private static $_defaultOptions = array();
    
    /**
     * The DSN associated to this connection.
     * 
     * @var string
     */
    private $_dsn;
    
    /**
     * The options associated to this connection.
     * 
     * @var array
     */
    private $_options;
    
    /**
     * Constructs a new connection and sets defaults.
     * 
     * @param string $dsn     The DSN to use for connecting.
     * @param array  $options The options to use for the connection.
     * 
     * @return Europa_Mongo_Connection
     */
    public function __construct($dsn = null, array $options = array())
    {
        // the DSN is the default if not specified
        if (!$dsn) {
            $dsn = self::getDefaultDsn();
        }
        
        // set the DSN
        $this->_dsn = $dsn;
        
        // merge the default options
        $this->_options = array_merge(self::getDefaultOptions(), $options);
        
        // force persistent connections
        $this->_options['persist'] = $this->_dsn;
        
        // connect
        try {
            parent::__construct($this->_dsn, $this->_options);
        } catch (Exception $e) {
            throw new Europa_Mongo_Exception(
                "Could not connect to {$this->_dsn}. Mesage: {$e->getMessage()}"
            );
        }
    }
    
    /**
     * Ensures that the connection is closed upon garbage collection.
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * Returns the specified database.
     * 
     * @param string $name The name of the database to return.
     * 
     * @return Europa_Mongo_Db
     */
    public function __get($name)
    {
        return $this->selectDb($name);
    }
    
    /**
     * Returns the DSN associated to this connection.
     * 
     * @return string
     */
    public function getDsn()
    {
        return $this->_dsn;
    }
    
    /**
     * Returns the options associated to this connection.
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Returns the specified database.
     * 
     * @param string $name The name of the database to return.
     * 
     * @return Europa_Mongo_Db
     */
    public function selectDb($name)
    {
        return new Europa_Mongo_Db($this, $name);
    }
    
    /**
     * Selects the specified collection.
     * 
     * @param string $dbName         The name of the database the collection belongs to.
     * @param string $collectionName The name of the collection.
     * 
     * @return Europa_Mongo_Collection
     */
    public function selectCollection($dbName, $collectionName)
    {
        return new Europa_Mongo_Collection($this->$dbName, $collectionName);
    }
    
    /**
     * Sets the specified connection information.
     * 
     * @param string $name    The name of the connection.
     * @param string $dsn     The connection to set.
     * @param array  $options The options to use.
     * 
     * @return void
     */
    public static function set($name = null, $dsn = null, array $options = array())
    {
        // default name
        if (!$name) {
            $name = self::getDefaultName();
        }
        
        // default dsn
        if (!$dsn) {
            $dsn = self::getDefaultDsn();
        }
        
        // default options
        $options = array_merge(self::getDefaultOptions(), $options);
        
        // set the connection
        self::$_connections[$name] = array('dsn' => $dsn, 'options' => $options);
    }
    
    /**
     * Returns the specified connection instance.
     * 
     * @param string $name The name of the connection. If no connections are present, one is
     *                     created using the given name.
     * 
     * @return Europa_Mongo_Connection
     */
    public static function get($name = null)
    {
        // default name
        if (!$name) {
            $name = self::getDefaultName();
        }
        
        // set a default connection if none exist
        if (!self::$_connections) {
            self::set($name);
        }
        
        // check to make sure the connection exists
        if (!self::has($name)) {
            throw new Europa_Mongo_Exception(
                "Cannot get connection {$name}. It doesn't exist!"
            );
        }
        
        // connect and return
        $conn = self::$_connections[$name];
        return new self($conn['dsn'], $conn['options']);
    }
    
    /**
     * Removes the specified connection.
     * 
     * @param string $name The name of the connection to remove.
     * 
     * @return void
     */
    public static function remove($name = null)
    {
        // default name
        if (!$name) {
            $name = self::getDefaultName();
        }
        
        if (self::has($name)) {
            unset(self::$_connections[$name]);
        }
    }
    
    /**
     * Returns whether or not the specified connection exists.
     * 
     * @param string $name The connection name to check for.
     * 
     * @return bool
     */
    public static function has($name = 'default')
    {
        return isset(self::$_connections[$name]);
    }
    
    /**
     * Sets a default connection name to use if none is specified.
     * 
     * @param string $name The name of the default connection.
     * 
     * @return void
     */
    public static function setDefaultName($name)
    {
        self::$_defaultName = $name;
    }
    
    /**
     * Returns the name of the default connection.
     * 
     * @return string
     */
    public static function getDefaultName()
    {
        return self::$_defaultName;
    }
    
    /**
     * Sets the default DSN string.
     * 
     * @param string $dsn The default DSN string to use.
     * 
     * @return void
     */
    public static function setDefaultDsn($dsn)
    {
        self::$_defaultDsn = $dsn;
    }
    
    /**
     * Returns the default DSN string.
     * 
     * @return string
     */
    public static function getDefaultDsn()
    {
        return self::$_defaultDsn;
    }
    
    /**
     * Sets the default options.
     * 
     * @param array $options The default options to use.
     * 
     * @return void
     */
    public static function setDefaultOptions(array $options)
    {
        self::$_defaultOptions = $options;
    }
    
    /**
     * Returns the default options.
     * 
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$_defaultOptions;
    }
}