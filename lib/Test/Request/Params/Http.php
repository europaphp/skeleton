<?php

class Test_Request_Params_Http extends Europa_Unit_Test
{	
	protected $_request;
	
	public function setUp()
	{
		$this->_request = new Europa_Request_Http;
		$this->_request->setParams(array('cascade1' => false, 'cascade2' => true))
		               ->setParams(array('cascade1' => true, 'controller' => 'customcontroller'));
	}
	
	public function testHttpParamCascading()
	{
		return $this->_request->cascade1 === true
		    && $this->_request->cascade2 === true;
	}
	
	public function testHttpParamSetting()
	{
		$this->_request->testSet1 = true;
		return $this->_request->testSet1 == true;
	}
	
	public function testHttpParamBulkSettingFromArray()
	{
		$this->_request->setParams(array('bulk1' => true));
		return $this->_request->bulk1 === true;
	}

	public function testHttpParamBulkSettingFromClass()
	{
		$class = new stdClass;
		$class->bulk2 = true;
		$this->_request->setParams($class);
		return $this->_request->bulk2 === true;
	}
	
	public function testHttpControllerSetting()
	{
		return $this->_request->getController() === 'customcontroller';
	}
	
	public function testHttpParamClearing()
	{
		$this->_request->clearParams();
		return $this->_request->bulk2 === null;
	}
}