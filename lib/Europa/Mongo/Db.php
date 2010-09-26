<?php

class Europa_Mongo_Db extends MongoDB
{
    /**
     * The connection to use for this database.
     * 
     * @var Europa_Mongo_Connection
     */
    private $_connection;
    
    /**
     * The name of the database.
     * 
     * @var string
     */
    private $_name;
    
    /**
     * Constructs the database and sets defaults.
     * 
     * @param Europa_Mongo_Connection $connection The connection to use.
     * @param string $name The name of the database.
     * @return Europa_Mongo_Db
     */
    public function __construct(Europa_Mongo_Connection $connection, $name)
    {
        parent::__construct($connection, $name);
        $this->_connection = $connection;
        $this->_name       = $name;
    }
    
    /**
     * Returns the specified collection.
     * 
     * @param string $name The name of the collection to get.
     * @return Europa_Mongo_Collection
     */
    public function __get($name)
    {
        return $this->selectCollection($name);
    }
    
    /**
     * Returns the specified collection.
     * 
     * @param string $name The name of the collection to get.
     * @return Europa_Mongo_Collection
     */
    public function selectCollection($name)
    {
        return new Europa_Mongo_Collection($this, $name);
    }
    
    /**
     * Returns the connection use for this database.
     * 
     * @return Europa_Mongo_Connection
     */
    final public function getConnection()
    {
        return $this->_connection;
    }
    
    /**
     * Returns the name of the database.
     * 
     * @return string
     */
    final public function getName()
    {
        return $this->_name;
    }
}