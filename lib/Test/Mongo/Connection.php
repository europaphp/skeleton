<?php

class Test_Mongo_Connection extends Europa_Unit_Test
{
    private $_mongo;
    
    public function setUp()
    {
        $this->_mongo = Europa_Mongo_Connection::get();
    }
    
    public function tearDown()
    {
        $this->_mongo->testdb->drop();
    }
    
    public function testConnectionSetting()
    {
        Europa_Mongo_Connection::set('testconnection');
        return Europa_Mongo_Connection::has('testconnection');
    }
    
    public function testConnectionRemoving()
    {
        Europa_Mongo_Connection::remove('testconnection');
        return !Europa_Mongo_Connection::has('testconnection');
    }
    
    public function testGetDb()
    {
        return $this->_mongo->testdb instanceof Europa_Mongo_Db;
    }
}