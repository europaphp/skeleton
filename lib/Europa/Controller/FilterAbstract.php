<?php

namespace Europa\Controller;

class FilterAbstract implements FilterInterface
{
	protected $controller;

	protected $method;

	protected $params;

	public function __construct(\Europa\Controller $controller, $method, array $params = array())
	{
		$this->controller = $controller;
		$this->method     = $method;
		$this->params     = $params;
	}

	public function filter()
	{
		
	}

	public function method()
	{
		return $this->method;
	}

	public function params()
	{
		return $this->params;
	}
}