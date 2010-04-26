<?php

/**
 * An example controller sketching some of the features that can be implemented.
 * 
 * Europa_Controller_Abstract is an optional base class, but provides a few
 * default properties and methods to make your life a bit easier.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class IndexController extends Europa_Controller
{
	/**
	 * The constructor. Similar to init, but init allows a return value
	 * besides the controller instance.
	 * 
	 * @return IndexController
	 */
	public function __construct()
	{
		$this->request = Europa_Request_Http::getActiveInstance();
		$this->layout  = $this->request->getLayout();
		$this->view    = $this->request->getView();
	}
	
	/**
	 * The destructor.
	 * 
	 * @return void
	 */
	public function __destruct()
	{
		
	}
	
	/**
	 * Traps any calls to an undefined method/action. This will get called after
	 * IndexController->init().
	 * 
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function __call($name, $args)
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
		$this->layout->title = 'Thanks for choosing EuropaPHP';
	}
	
	/**
	 * A method that get's called after the action, but before rendering.
	 * 
	 * @return void
	 */
	public function preRender()
	{
		
	}
	
	/**
	 * A method that gets called after rendering the layout and view.
	 * 
	 * @return void
	 */
	public function postRender()
	{
		
	}
	
	/**
	 * Gets called pre-rendering of the view. Any variables returned here
	 * in an array are applied to the view. If false is returned, the view
	 * is then disabled and not rendered.
	 * 
	 * Parameters passed to actions are retrieved using
	 * Europa_Route->getParam() and are casted according to type. For example,
	 * index.php?controller=index&action=index&msg=test would make $msg
	 * 'test' instead of 'Welcome to EuropaPHP!'. 
	 * 
	 * If a parameter is required and isn't passed either via the route, $_GET
	 * or $_POST, then an exception will be thrown notifying as much.
	 * 
	 * @param string $msg
	 * @return array|false|void
	 */
	public function indexAction($msg = "You won't be sorry")
	{
		$this->view->msg = $msg;
	}
}