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
     * The default connection.
     * 
     * @var Europa_Mongo_Connection
     */
    private static $_defaultConnection;
    
    /**
     * Constructs a new connection and sets defaults.
     * 
     * @param string $dsn
     * @param array $options
     * @return Europa_Mongo_Connection
     */
    public function __construct($dsn = 'localhost:27017', array $options = array())
    {
        $dsn = $this->_formatDsn($dsn);
        try {
            parent::__construct($dsn, $options);
        } catch (Exception $e) {
            throw new Europa_Mongo_Exception(
                'Could not connect to database with message: ' . $e->getMessage(),
                $e->getCode()
            );
        }
        
        // set a default connection
        if (!self::hasDefault()) {
            self::setDefault($this);
        }
    }
    
    /**
     * Returns the specified database.
     * 
     * @param string $name
     * @return Europa_Mongo_Db
     */
    public function __get($name)
    {
        return $this->selectDb($name);
    }
    
    /**
     * Returns the specified database.
     * 
     * @param string $name
     * @return Europa_Mongo_Db
     */
    public function selectDb($name)
    {
        return new Europa_Mongo_Db($this, $name);
    }
    
    /**
     * Selects the specified collection.
     * 
     * @param string $dbName
     * @param string $collectionName
     * @return Europa_Mongo_Collection
     */
    public function selectCollection($dbName, $collectionName)
    {
        return new Europa_Mongo_Collection($this->$dbName, $collectionName);
    }
    
    /**
     * Sets the specified connection.
     * 
     * @param string $name
     * @param Europa_Mongo_Connection $connection
     * @return Europa_Mongo_Connection
     */
    public static function set($name, Europa_Mongo_Connection $connection)
    {
        self::$_connections[$name] = $connection;
        return $connection;
    }
    
    /**
     * Returns the specified connection.
     * 
     * @param string $name
     * @return Europa_Mongo_Connection
     */
    public static function get($name)
    {
        if (!self::has($name)) {
            throw new Europa_Mongo_Exception(
                'Cannot get connection ' . $name . '. It doesn\'t exist!'
            );
        }
        return self::$_connections[$name];
    }
    
    /**
     * Returns whether or not the specified connection exists.
     * 
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return isset(self::$_connections[$name]);
    }
    
    /**
     * Sets the default connection.
     * 
     * @param Europa_Mongo_Connection $connection
     * @return Europa_Mongo_Connection
     */
    public static function setDefault(Europa_Mongo_Connection $connection)
    {
        self::$_defaultConnection = $connection;
        return $connection;
    }
    
    /**
     * Returns the default connection.
     * 
     * @return Europa_Mongo_Connection
     */
    public static function getDefault()
    {
        return self::$_defaultConnection;
    }
    
    /**
     * Returns whether or not there is a default connection.
     * 
     * @return bool
     */
    public static function hasDefault()
    {
        return self::getDefault() instanceof self;
    }
    
    /**
     * Normalizes the dsn. Makes for passing a simpler DSN to
     * the constructor.
     * 
     * @param string $dsn
     * @return string
     */
    private function _formatDsn($dsn)
    {
        $dsn = str_replace('mongodb://', '', $dsn);
        $dsn = trim($dsn, '/');
        return 'mongodb://' . $dsn;
    }
}