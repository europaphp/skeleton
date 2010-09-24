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
}