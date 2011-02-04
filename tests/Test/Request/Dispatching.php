<?php

class Test_Request_Dispatching extends Testes_Test
{
    static public $request = false;
    
    public function testGettingActiveInstance()
    {
        $request = new \Europa\Request\Http;
        $request->setController('test');
        $request->dispatch();
        
        $this->assert(
            self::$request === $request,
            'Unable to retrieve active instance.'
        );
    }
}

class TestController extends \Europa\Controller
{
    public function get()
    {
        Test_Request_Dispatching::$request = \Europa\Request::getCurrent();
    }
    
    public function __toString()
    {
        
    }
}