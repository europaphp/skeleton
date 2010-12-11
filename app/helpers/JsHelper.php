<?php

class jsHelper
{
	private $_layout;
	
	private $_view;
	
	public function __construct(Europa_View $view)
	{
		$request = new Europa_Request_Http;
		$this->_layout = 'js/' . $view->getScript() . '.js';
		$this->_view   = 'js/' . Europa_String::create($request->getController())->toClass()->replace('_', DIRECTORY_SEPARATOR) . 'View.js';
	}
	
	public function __toString()
	{
		$js = '';
		
		if (file_exists('./' . $this->_layout)) {
			$js .= '<link rel="stylesheet" type="text/js" href="/' 
			     .  Europa_Request_Http::root()
			     .  '/'
			     .  $this->_layout 
			     .  '" />'
			     .  "\n";
		}
		
		if (file_exists('./' . $this->_view)) {
			$js .= '<link rel="stylesheet" type="text/js" href="/' 
			     .  Europa_Request_Http::root()
			     .  '/'
			     .  $this->_view 
			     .  '" />'
			     .  "\n";
		}
		
		return $js;
	}
}