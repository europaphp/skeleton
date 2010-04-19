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
 * @package  Europa_Controller
 * @license  (c) 2010 Trey Shugart <treshugart@gmail.com>
 * @link     http://europaphp.org/license
 */
abstract class Europa_Controller
{
	/**
	 * Returns the active controller
	 * 
	 * @return Europa_Request
	 */
	protected function _getRequest()
	{
		return Europa_Request::getActiveInstance();
	}
	
	/**
	 * Returns the set layout.
	 * 
	 * @return Europa_View_Abstract
	 */
	protected function _getLayout()
	{
		return $this->_getRequest()->getLayout();
	}
	
	/**
	 * Returns the set route.
	 * 
	 * @return Europa_Route
	 */
	protected function _getRoute()
	{
		return $this->_getRequest()->getRoute();
	}
	
	/**
	 * Returns the set view.
	 * 
	 * @return Europa_View_Abstract
	 */
	protected function _getView()
	{
		return $this->_getRequest()->getView();
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
	protected function _redirect($uri = '/', $europaRelative = true)
	{
		if ($europaRelative) {
			$uri = $this->_getView()->uri($uri);
		}
		
		header('Location: ' . $uri);
		
		exit;
	}
}