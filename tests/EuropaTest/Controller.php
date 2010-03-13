<?php

class EuropaTest_Controller extends Europa_Unit_Group
{
	protected $controller;
	
	/**
	 * Requires the appropriate files for running the test.
	 * 
	 * @return void
	 */
	public function setUp()
	{
		$this->controller = new Europa_Controller;
	}
	
	/**
	 * Tests the integrity of Europa_Controller::__construct() and makes sure
	 * that the default properties are appropriately set.
	 * 
	 * @return bool
	 */
	public function testConstructor()
	{
		$hasView   = $this->controller->getView()   instanceof Europa_View;
		$hasLayout = $this->controller->getLayout() instanceof Europa_View;
		
		return $hasView && $hasLayout;
	}
	
	public function testSetLayout()
	{
		$this->controller->setLayout(null);
		
		return $this->controller->getLayout() === null;
	}
	
	public function testSetView()
	{
		$this->controller->setView(null);

		return $this->controller->getView() === null;
	}
	
	public function testImplicitRoute()
	{
		$this->controller->setRoute('test', new Europa_Route(
			'test/([^/]*)/?([^/]*)/?',
			array('controller', 'action'),
			'index.php?controller=:controller&action=:action'
		));
		
		return $this->controller->getRoute('test') instanceof Europa_Route;
	}
	
	public function testExplicitRoute()
	{
		$this->controller->setRoute($this->controller->getRoute('test'));
		
		return $this->controller->getRoute() instanceof Europa_Route;
	}
	
	public function testDefaultRoute()
	{
		return $this->controller->getDefaultRoute() instanceof Europa_Route;
	}
}