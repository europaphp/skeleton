<?php

class Test_Request_Dispatching extends Europa_Unit_Test
{
	static public $request = false;
	
	public function testGettingActiveInstance()
	{
		$request = new Europa_Request_Http;
		$request->setController('test');
		$request->dispatch();
		return self::$request === $request;
	}
}

class TestController extends Europa_Controller
{
	public function action()
	{
		Test_Request_Dispatching::$request = Europa_Request::getActiveInstance();
	}
	
	public function __toString()
	{
		
	}
}