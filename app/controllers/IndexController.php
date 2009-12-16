<?php

class IndexController
{
	/**
	 * The constructor. Similar to init, but init allows a return value
	 * besides the controller instance.
	 */
	public function __construct()
	{
		$this->dispatcher = Europa_Dispatcher::getActiveInstance();
	}
	
	/**
	 * Traps any calls to an undefined method. This will get called after
	 * IndexController->init().
	 * 
	 * @return void
	 */
	public function __call($name, $args)
	{
		$this->dispatcher->getLayout()->title = 'Error: 404 - Not Found';
		$this->dispatcher->getView()->msg     = 'The requested page was unable to be found';
	}
	
	/**
	 * Destructor. Does not need to be defined, but exists as a hook
	 * for any post-dispatch events.
	 * 
	 * @return IndexController
	 */
	public function __destruct()
	{
		
	}
	
	/**
	 * Gets called pre-rendering of the layout. Any variables returned here
	 * in an array are applied to the layout. If false is returned, the 
	 * layout is disabled and not rendered.
	 * 
	 * @return array|false|void
	 */
	public function init()
	{
		return array('title' => 'EuropaPHP');
	}
	
	/**
	 * Gets called pre-rendering of the view. Any varables returned here
	 * in an array are applied to the view. If false is returned, the view
	 * is then disabled and not rendered.
	 * 
	 * @return array|false|void
	 */
	public function indexAction($msg = 'This is the default message.')
	{
		return array('msg' => $msg);
	}
}