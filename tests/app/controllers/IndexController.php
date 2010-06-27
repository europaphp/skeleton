<?php

class IndexController extends Europa_Controller_Action
{
	public function indexAction($test = 'Test', $verbose = false)
	{
		$class = new $test;
		$class->run();
		$this->_view->test    = $class;
		$this->_view->verbose = $verbose;
	}
}