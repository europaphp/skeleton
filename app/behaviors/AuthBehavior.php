<?php

class AuthBehavior
{
	protected $controller;

	public function __construct(\Europa\Controller $controller, $method, array $params = array())
	{
		$this->controller = $controller;

		if (!$this->isValidUser()) {
			$this->redirect();
		}
	}

	public function isValidUser()
	{
		return false;
	}

	public function redirect()
	{
		$this->controller->redirect('index.php/index');
	}
}