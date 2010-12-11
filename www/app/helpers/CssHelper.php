<?php

class CssHelper
{
	private $_layout;
	
	private $_view;
	
	public function __construct(Europa_View $view)
	{
		$request = new Europa_Request_Http;
		$this->_layout = 'css/' . $view->getScript() . '.css';
		$this->_view   = 'css/' . Europa_String::create($request->getController())->toClass()->replace('_', DIRECTORY_SEPARATOR) . 'View.css';
	}
	
	public function __toString()
	{
		$css = '';
		
		if (file_exists('./' . $this->_layout)) {
			$css .= '<link rel="stylesheet" type="text/css" href="/' 
			     .  Europa_Request_Http::root()
			     .  '/'
			     .  $this->_layout 
			     .  '" />'
			     .  "\n";
		}
		
		if (file_exists('./' . $this->_view)) {
			$css .= '<link rel="stylesheet" type="text/css" href="/' 
			     .  Europa_Request_Http::root()
			     .  '/'
			     .  $this->_view 
			     .  '" />'
			     .  "\n";
		}
		
		return $css;
	}
}