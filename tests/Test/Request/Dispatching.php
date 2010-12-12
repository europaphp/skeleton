<?php

class Test_Request_Dispatching extends Testes_Test
{
	static public $request = false;
	
	public function testGettingActiveInstance()
	{
		$request = new Europa_Request_Http;
		$request->setController('test');
		$request->dispatch();
		
		$this->assert(
		    self::$request === $request,
		    'Unable to retrieve active instance.'
		);
	}
}

class TestController extends Europa_Controller
{
	public function get()
	{
		Test_Request_Dispatching::$request = Europa_Request::getCurrent();
	}
	
	public function __toString()
	{
		
	}
}