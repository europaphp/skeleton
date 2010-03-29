<?php

/**
 * @author Trey Shugart
 */

/**
 * This is an optional base class for controller instances. it provides
 * a default set of properties accessible within the controller as well
 * as utility methods for controlling application flow from within
 * controllers.
 * 
 * @package Europa
 * @subpackage Controller
 */
abstract class Europa_Controller_Abstract
{
	/**
	 * An instance of the currently dispatching controller.
	 * 
	 * @var Europa_Controller
	 */
	protected $_controller;
	
	/**
	 * An instance of the route that is being dispatched.
	 * 
	 * @var Europa_Route
	 */
	protected $_route;
	
	/**
	 * An instance of the layout. Generally this is an instance of
	 * Europa_View.
	 * 
	 * @var Europa_View
	 */
	protected $_layout;
	
	/**
	 * An instance of the view. Generally this is an instance of 
	 * Europa_View.
	 * 
	 * @var Europa_View
	 */
	protected $_view;
	
	/**
	 * Constructs the controller class and sets default properties.
	 * 
	 * @return Europa_Controller_Abstract
	 */
	public function __construct()
	{
		$this->_controller   = Europa_Controller::getActiveInstance();
		$this->_route        = $this->_controller->getRoute();
		$this->_layout       = $this->_controller->getLayout();
		$this->_view         = $this->_controller->getView();
		$this->_layout->view = $this->_view;
	}
	
	/**
	 * Forwards the current request to a particular action.
	 * 
	 * @param string $action
	 * @return void
	 */
	protected function forward($action)
	{
		// force a route
		$this->_controller->setRoute(
			new Europa_Route(
				array(
					'controller' => $this->_route->getParam('controller'),
					'action'     => $action
				)
			)
		);
		
		// by dispatching here, it cancels the last dispatch call
		$this->_controller->dispatch();
		
		exit;
	}
	
	/**
	 * Redirects the client to the specified URI.
	 * 
	 * The URI will always be transformed into a Europa URI unless 
	 * $europaRelative is set to false. The script will automatically
	 * be terminated after the redirect.
	 * 
	 * @param string $uri The URI to redirect to.
	 * @param bool $europaRelative Whether or not to automatically transform
	 * the passed URI into a Europa URI.
	 * @return void
	 */
	protected function redirect($uri = '/', $europaRelative = true)
	{
		if ($europaRelative) {
			$uri = $this->_view->uri($uri);
		}
		
		header('Location: ' . $uri);
		
		exit;
	}
}