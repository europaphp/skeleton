<?php

class JsHelper implements Europa_View_Helper
{
	protected $_layout;
	
	protected $_view;
	
	public function __construct(Europa_View $view, array $args = array())
	{
		$request = new Europa_Request_Http;
		$this->_layout = './js/' . $view->getScript() . '.js';
		$this->_view   = './js/' . Europa_String::create($request->getController())->toClass()->replace('_', DIRECTORY_SEPARATOR) . 'View.js';
	}
	
	public function __toString()
	{
		$js = '';
		
		if (file_exists($this->_layout)) {
			$js .= '<script type="text/javascript" src="'
			    . $this->_layout 
			    . '"></script>'
			    .  "\n";
		}
		
		if (file_exists($this->_view)) {
			$js .= '<script type="text/javascript" src="'
			    . $this->_view 
			    . '"></script>'
			    .  "\n";
		}
		
		return $js;
	}
}