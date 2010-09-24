<?php

class CssHelper implements Europa_View_Helper
{
	protected $_layout;
	
	protected $_view;
	
	public function __construct(Europa_View $view, array $args = array())
	{
		$request = new Europa_Request_Http;
		$this->_layout = './css/' . $view->getScript() . '.css';
		$this->_view   = './css/' . Europa_String::create($request->getController())->toClass()->replace('_', DIRECTORY_SEPARATOR) . 'View.css';
	}
	
	public function __toString()
	{
		$css = '';
		
		if (file_exists($this->_layout)) {
			$css .= '<link rel="stylesheet" type="text/css" href="' 
			     . $this->_layout 
			     . '" />'
			     .  "\n";
		}
		
		if (file_exists($this->_view)) {
			$css .= '<link rel="stylesheet" type="text/css" href="' 
			     . $this->_view 
			     . '" />'
			     .  "\n";
		}
		
		return $css;
	}
}