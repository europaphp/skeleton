<?php

class Test_Route_Simple extends Testes_Test
{
	public function testMatch()
	{
		$route = new Europa_Route_Simple(':controller/:action');
		$this->assert(
		    $route->query('some-controller/some-action') !== false,
		    'Matching not working.'
		);
	}
	
	public function testNonMatch()
	{
		$route = new Europa_Route_Simple(':controller/:action');
		$this->assert(
		    $route->query('some-controller/some-action/some-parameter') === false,
		    'Not-matching not working.'
		);
	}
	
	public function testWildcardWithSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action/*');
		$valid = $route->query('some-controller/some-action/') !== false
		      && $route->query('some-controller/some-action/some/other/stuff') !== false;
		
		$this->assert($valid, 'Wildcard with slash not working.');
	}
	
	public function testWildcardWithoutSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action*');
		$valid = $route->query('some-controller/some-action') !== false
		      && $route->query('some-controller/some-action/some/other/stuff') !== false;
		
		$this->assert($valid, 'Wildcard without slash not working.');
	}
	
	public function testRequireEndingSlash()
	{
		$route = new Europa_Route_Simple(':controller/:action/');
		$this->assert(
		    $route->query('some-controller/some-action') === false,
		    'Required ending slash not working.'
		);
	}
	
	public function testParameterBinding()
	{
		$route  = new Europa_Route_Simple(':controller/:action');
		$params = $route->query('test-controller/test-action');
		
		if (!$params) {
			return false;
		}
		
		$valid = $params['controller'] === 'test-controller'
		      && $params['action']     === 'test-action';
		
		$this->assert($valid, 'Parameter binding not working.');
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
		
		$valid = $params['controller'] === 'test-controller'
		      && $params['action']     === 'test-action'
		      && $params['user']       === 'testuser';
		
		$this->assert($valid, 'Default parameter binding not working.');
	}
	
	public function testDynamicParameterBinding()
	{
		$route  = new Europa_Route_Simple(':controller/:action*');
		$params = $route->query('controller/action/param1:value1/param2:value2');
		
		if (!$params) {
			return false;
		}
		
		$valid = $params['param1'] === 'value1'
		      && $params['param2'] === 'value2';
		
		$this->assert($valid, 'Dynamic parameter binding not working.');
	}
	
	public function testReverseEngineering()
	{
		$route = new Europa_Route_Simple('user/:username');
		$this->assert(
		    $route->reverse(array('username' => 'testuser')) === 'user/testuser',
		    'Reverse engineering not working.'
		);
	}
}