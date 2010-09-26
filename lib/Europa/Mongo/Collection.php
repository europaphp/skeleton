<?php

class Europa_Mongo_Collection extends MongoCollection
{
    /**
     * The database to use for the collection.
     * 
     * @var Europa_Mongo_Db
     */
    private $_db;
    
    /**
     * The name of the collection.
     * 
     * @var string
     */
    private $_name;
    
    public function __construct(Europa_Mongo_Db $db, $name)
    {
        parent::__construct($db, $name);
        $this->_db   = $db;
        $this->_name = $name;
    }
    
    public function find(array $query = array(), array $fields = array())
    {
        return new Europa_Mongo_Cursor($this->getDb()->getConnection(), $this->__toString(), $query, $fields);
    }
    
    public function findOne(array $query = array(), array $fields = array())
    {
        return $this->find($query, $fields)->limit(1)->offsetGet(0);
    }
    
    final public function getDb()
    {
        return $this->_db;
    }
    
    final public function getName()
    {
        return $this->_name;
    }
}