<?php

class Test_Route_Simple extends Europa_Unit_Test
{
	public function setUp()
	{
		
	}
	
	public function testMatch()
	{
		$route = new Europa_Route_Simple(':controller/:action');
		return $route->query('some-controller/some-action') !== false;
	}
	
	public function testNonMatch()
	{
		$route = new Europa_Route_Simple(':controller/:action');
		return $route->query('some-controller/some-action/some-parameter') === false;
	}
	
	public function testWildcardWithSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action/*');
		return $route->query('some-controller/some-action/') !== false
		    && $route->query('some-controller/some-action/some/other/stuff') !== false;
	}
	
	public function testWildcardWithoutSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action*');
		return $route->query('some-controller/some-action') !== false
		    && $route->query('some-controller/some-action/some/other/stuff') !== false;;
	}
	
	public function testRequireEndingSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action/');
		return $route->query('some-controller/some-action') === false;
	}
	
	public function testParameterBinding()
	{
		$route  = new Europa_Route_Simple(':controller/:action');
		$params = $route->query('test-controller/test-action');
		
		if (!$params) {
			return false;
		}
		
		return $params['controller'] === 'test-controller'
		    && $params['action']     === 'test-action';
	}
	
	public function testDefaultParameterBinding()
	{
		$route = new Europa_Route_Simple(
			'user/:user',
			array(
				'controller' => 'test-controller',
				'action'     => 'test-action'
			)
		);
		$params = $route->query('user/testuser');
		
		if (!$params) {
			return false;
		}
		
		return $params['controller'] === 'test-controller'
		    && $params['action']     === 'test-action';
	}
	
	public function testDynamicParameterBinding()
	{
		$route  = new Europa_Route_Simple(':controller/:action*');
		$params = $route->query('controller/action/param1:value1/param2:value2');
		
		if (!$params) {
			return false;
		}
		
		return $params['param1'] === 'value1'
		    && $params['param2'] === 'value2';
	}
	
	public function testReverseEngineering()
	{
		$route = new Europa_Route_Simple('user/:username');
		return $route->reverse(array('username' => 'testuser')) === 'user/testuser';
	}
	
	public function tearDown()
	{
		
	}
}