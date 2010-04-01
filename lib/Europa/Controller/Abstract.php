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
	 * Returns the active controller
	 * 
	 * @return Europa_Controller
	 */
	public function _getController()
	{
		return Europa_Controller::getActiveInstance();
	}
	
	/**
	 * Returns the set layout.
	 * 
	 * @return Europa_View_Abstract
	 */
	public function _getLayout()
	{
		return $this->_getController()->getLayout();
	}
	
	/**
	 * Returns the set route.
	 * 
	 * @return Europa_Route
	 */
	public function _getRoute()
	{
		return $this->_getController()->getRoute();
	}
	
	/**
	 * Returns the set view.
	 * 
	 * @return Europa_View_Abstract
	 */
	public function _getView()
	{
		return $this->_getController()->getView();
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