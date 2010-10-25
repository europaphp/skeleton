<?php

class Test_Mongo extends Europa_Unit_Suite
{
    public static $dsn = 'localhost:27017';
    
    public function setUp()
    {
        Europa_Mongo_Connection::setDefault(
            new Europa_Mongo_Connection(
                self::$dsn,
                array(
                    'persistent' => true
                )
            )
        );
    }
}