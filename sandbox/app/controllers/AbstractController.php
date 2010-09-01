<?php

abstract class AbstractController extends Europa_Controller
{
	private $_view;
	
	public function __construct($request)
	{
		parent::__construct($request);
		
		$controller = $request->getController();
		$controller = Europa_String::create($controller)->toClass()->replace('_', DIRECTORY_SEPARATOR);
		
		$this->_view = new Europa_View_Layout;
		$this->_view->setLayout(new Europa_View_Php('IndexLayout'));
		$this->_view->setView(new Europa_View_Php($controller . 'View'));
	}
	
	public function __toString()
	{
		return $this->_view->__toString();
	}
	
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	public function getView()
	{
		return $this->_view;
	}
}