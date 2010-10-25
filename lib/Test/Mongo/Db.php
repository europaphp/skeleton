<?php

class Test_Mongo_Db extends Europa_Unit_Test
{
    private $_mongo;
    
    public function setUp()
    {
        $this->_mongo = Europa_Mongo_Connection::getDefault();
    }
    
    public function tearDown()
    {
        $this->_mongo->testdb->drop();
    }
    
    public function testGetCollection()
    {
        return $this->_mongo->testdb->testcollection instanceof Europa_Mongo_Collection;
    }
    
    public function testGetName()
    {
        return $this->_mongo->testdb->getName()    === 'testdb'
            && $this->_mongo->testdb->__toString() === 'testdb';
    }
    
    public function testGetConnection()
    {
        return $this->_mongo->testdb->getConnection() instanceof Europa_Mongo_Connection;
    }
}