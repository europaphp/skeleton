<?php

/**
 * @author Trey Shugart
 */

/**
 * An example controller sketching some of the features that can be implemented.
 * 
 * Europa_Controller_Abstract is an optional base class, but provides a few
 * default properties and methods to make your life a bit easier.
 * 
 * @package IndexController
 */
class IndexController extends Europa_Controller_Abstract
{
	/**
	 * The constructor. Similar to init, but init allows a return value
	 * besides the controller instance.
	 * 
	 * @return IndexController
	 */
	public function __construct()
	{
		// do something cool
	}
	
	/**
	 * Traps any calls to an undefined method. This will get called after
	 * IndexController->init().
	 * 
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function __call($name, $args)
	{
		$this->_getLayout()->title = 'EuropaPHP - 404';
		$this->_getView()->msg     = 'The page you requested could not be found.';
		$this->_getView()->setScript('index/index');
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
			'title' => 'EuropaPHP',
			'view'  => $this->_getView()
		);
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
	public function indexAction($msg = 'Welcome to EuropaPHP!')
	{
		return array(
			'msg' => $msg
		);
	}
}