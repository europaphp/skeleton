<?php

class Test_Mongo_Connection extends Europa_Unit_Test
{
    private $_mongo;
    
    public function testConnection()
    {
        try {
            $this->_mongo = new Europa_Mongo_Connection;
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    public function testDefaultConnectionUponConstruction()
    {
        return Europa_Mongo_Connection::hasDefault();
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
    
    public function tearDown()
    {
        $this->_mongo->testdb->drop();
    }
}