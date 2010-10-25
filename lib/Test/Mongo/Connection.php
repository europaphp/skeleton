<?php

class Test_Mongo_Connection extends Europa_Unit_Test
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
    
    public function testConnectionSetting()
    {
        Europa_Mongo_Connection::set('testconnection', new Europa_Mongo_Connection);
        return Europa_Mongo_Connection::has('testconnection');
    }
    
    public function testGetDb()
    {
        return $this->_mongo->testdb instanceof Europa_Mongo_Db;
    }
}