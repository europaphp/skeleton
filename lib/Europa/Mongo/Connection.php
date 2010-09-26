<?php

class Europa_Mongo_Connection extends Mongo
{
    private static $_connections = array();
    
    private static $_defaultConnection;
    
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
    
    public function __get($name)
    {
        return $this->selectDb($name);
    }
    
    public function selectDb($name)
    {
        return new Europa_Mongo_Db($this, $name);
    }
    
    public function selectCollection($dbName, $collectionName)
    {
        return new Europa_Mongo_Collection($this->$dbName, $collectionName);
    }
    
    public static function exists($name)
    {
        return isset(self::$_connections[$name]);
    }
    
    public static function get($name)
    {
        if (!self::exists($name)) {
            throw new Europa_Mongo_Exception(
                'Cannot get connection ' . $name . '. It doesn\'t exist!'
            );
        }
        return self::$_connections[$name];
    }
    
    public static function hasDefault()
    {
        return self::$_defaultConnection instanceof self;
    }
    
    public static function set($name, Europa_Mongo_Connection $connection)
    {
        self::$_connections[$name] = $connection;
        return $connection;
    }
    
    public static function setDefault(Europa_Mongo_Connection $connection)
    {
        self::$_defaultConnection = $connection;
        return $connection;
    }
    
    public static function getDefault()
    {
        return self::$_defaultConnection;
    }
    
    private function _formatDsn($dsn)
    {
        $dsn = str_replace('mongodb://', '', $dsn);
        $dsn = trim($dsn, '/');
        return 'mongodb://' . $dsn;
    }
}