<?php

class IndexController extends Europa_Controller_Abstract
{
	/**
	 * The constructor. Similar to init, but init allows a return value
	 * besides the controller instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Traps any calls to an undefined method. This will get called after
	 * IndexController->init().
	 * 
	 * @return void
	 */
	public function __call($name, $args)
	{
		$this->layout->title = 'EuropaPHP - 404';
		$this->view->msg     = 'The page you requested could not be found.';
		$this->view->setScript('index/index');
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
		return array(
			'title' => 'EuropaPHP'
		);
	}
	
	/**
	 * Gets called pre-rendering of the view. Any variables returned here
	 * in an array are applied to the view. If false is returned, the view
	 * is then disabled and not rendered.
	 * 
	 * @return array|false|void
	 */
	public function indexAction($msg = 'Welcome to EuropaPHP!')
	{
		return array(
			'msg' => $msg
		);
	}
}