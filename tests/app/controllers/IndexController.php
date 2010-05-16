<?php

class IndexController extends Europa_Controller
{
	public function indexAction($test = 'AllTests', $verbose = false)
	{
		$class = new $test;
		$class->run();
		$this->_view->test    = $class;
		$this->_view->verbose = $verbose;
	}
}