<?php

namespace Europa\Controller;

interface FilterInterface
{
	public function __construct(\Europa\Controller $controller, $method, array $params = array());

	public function filter();

	public function method();

	public function params();
}