<?php

class Test_Request_Http_SetLayout extends Europa_Unit_Test
{
	private $_request;
	
	public function setUp()
	{
		$this->_request = new Europa_Request_Http;
		$this->_request->setLayout(null);
	}
	
	public function run()
	{
		return $this->_request->getLayout() === null;
	}
	
	public function tearDown()
	{
		unset($this->_request);
	}
}