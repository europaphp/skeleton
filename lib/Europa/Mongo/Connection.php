<?php

class Europa_Mongo_Connection extends Mongo
{
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
    }
    
    private function _formatDsn($dsn)
    {
        return 'mongodb://' . trim($dsn, '/');
    }
}