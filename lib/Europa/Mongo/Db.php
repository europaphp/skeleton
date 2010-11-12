<?php

/**
 * The MongoDB connection class. Allows management and assigning
 * of mutiple connections as well as default connections.
 * 
 * @category Mongo
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
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
     * The subclass to use when returning a collection.
     * 
     * @var string
     */
    private $_class;
    
    /**
     * Constructs the database and sets defaults.
     * 
     * @param Europa_Mongo_Connection $connection The connection to use.
     * @param string                  $name       The name of the database.
     * 
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
     * 
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
     * 
     * @return Europa_Mongo_Collection
     */
    public function selectCollection($name)
    {
        $class = $this->getClass();
        $class = new $class($this, $name);
        if (!$class instanceof Europa_Mongo_Collection) {
            throw new Europa_Mongo_Exception(
                'Collection class must be an instance or subclass of Europa_Mongo_Collection.'
            );
        }
        return $class;
    }
    
    /**
     * Returns the connection use for this database.
     * 
     * @return Europa_Mongo_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }
    
    /**
     * Returns the name of the database.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Sets the class to use for collection subclasses.
     * 
     * @param string $class The class to use for subclasses.
     * 
     * @return Europa_Mongo_Db
     */
    public function setClass($class)
    {
        $this->_class = $class;
        return $this;
    }
    
    /**
     * Returns the classname being used for collections.
     * 
     * @return string
     */
    public function getClass()
    {
        if (!$this->_class) {
            $this->setClass('Europa_Mongo_Collection');
        }
        return $this->_class;
    }
}