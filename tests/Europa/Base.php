<?php

class UnitTest_Europa_Base extends Europa_Unit
{
	public function __construct()
	{
		// we use Europa_Controller because it uses Europa_Base
		$this->testClass = new Europa_Controller;
	}
	
	
	
	public function testSettingConfigByKeyValAndGettingByKey()
	{
		$this->testClass->setConfig('testKeyVal', true);
		
		return $this->testClass->getConfig('testKeyVal');
	}
	
	public function testSettingByArrayAndGettingWithNoArguments()
	{
		$this->testClass->setConfig(array(
				'testByArray1' => true,
				'testByArray2' => true
			));
		
		$config = $this->testClass->getConfig();
		
		return $config['testByArray1'] && $config['testByArray2'];
	}
	
	public function testSettingAndGettingAnInstance()
	{
		$this->testClass->setInstance('myInstance');
		
		return $this->getInstance('myInstance') instanceof Europa_Controller;
	}
}