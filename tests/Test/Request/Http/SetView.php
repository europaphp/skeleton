<?php

class Test_Request_Http_SetView extends Europa_Unit_Test
{
	private $_request;
	
	public function setUp()
	{
		$this->_request = new Europa_Request_Http;
		$this->_request->setView(null);
	}
	
	public function run()
	{
		return $this->_request->getView() === null;
	}
	
	public function tearDown()
	{
		unset($this->_request);
	}
}