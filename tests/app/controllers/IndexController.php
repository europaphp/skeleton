<?php

class IndexController
{
	private $_request;
	
	private $_layout;
	
	private $_view;
	
	public function __construct()
	{
		$this->_request = Europa_Request::getActiveInstance();
		$this->_layout  = $this->_request->getLayout();
		$this->_view    = $this->_request->getView();
	}
	
	public function indexAction($test = 'AllTests', $verbose = false)
	{
		$class = new $test;
		$class->run();
		$this->_view->test    = $class;
		$this->_view->verbose = $verbose;
	}
}