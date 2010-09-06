<?php

class IndexController extends Europa_Controller_Action
{
	public function indexAction($test = 'Test', $verbose = false)
	{
		$class = new $test;
		$class->run();
		
		$view = new Europa_View_Php('TestView');
		$view->setParams(
			array(
				'test'    => $class,
				'verbose' => $verbose
			)
		);
		
		$this->setView($view);
	}
}