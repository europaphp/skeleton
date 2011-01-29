<?php

class CastParamBehavior
{
	protected $controller;

	protected $method;

	protected $params;

	public function __construct(\Europa\Controller $controller, $method, array $params = array())
	{
		$this->controller = $controller;
		$this->method     = $method;
		$this->params     = $params;
		$this->cast();
	}

	public function cast()
	{
		foreach ($this->params as $name => $value) {
			
		}
	}
}