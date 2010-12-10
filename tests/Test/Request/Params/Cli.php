<?php

class Test_Request_Params_Cli extends Europa_Unit_Test
{
	public function setUp()
	{
		// simulate argv
		$_SERVER['argv'] = array(
			'test-script.php',
			'-f',
			'-flag2',
			'--flag3',
			'-p', 'param1',
			'--param2', 'param2',
			'--param3', 'param3',
			'-param3', 'overridden',
			'--controller', 'customcontroller'
		);
		$this->_request = new Europa_Request_Cli;
	}
	
	public function testCliFlag1()
	{
		return $this->_request->f === true;
	}
	
	public function testCliFlag2()
	{
		return $this->_request->flag2 === true;
	}
	
	public function testCliFlag3()
	{
		return $this->_request->flag3 === true;
	}
	
	public function testCliParam1()
	{
		return $this->_request->p === 'param1';
	}
	
	public function testCliParam2()
	{
		return $this->_request->param2 === 'param2';
	}
	
	public function testCliParam3()
	{
		return $this->_request->param3 === 'overridden';
	}
	
	public function testCliControllerSetting()
	{
		return $this->_request->getController() === 'customcontroller';
	}
	
	public function testCliParamClearing()
	{
		$this->_request->clearParams();
		return $this->_request->param3 === null;
	}
}