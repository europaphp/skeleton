<?php

abstract class Europa_Controller_Abstract
{
	protected
		$controller,
		$route,
		$layout,
		$view;
	
	public function __construct()
	{
		$this->controller   = Europa_Controller::getActiveInstance();
		$this->route        = $this->controller->getRoute();
		$this->layout       = $this->controller->getLayout();
		$this->view         = $this->controller->getView();
		$this->layout->view = $this->view;
	}
	
	protected function forward($action)
	{
		// force a route
		$this->controller->setRoute(new Europa_Route(array(
			'controller' => $this->route->getParam('controller'),
			'action'     => $action
		)));
		
		// by dispatching here, it cancels the last dispatch call
		$this->controller->dispatch();
		
		exit;
	}
	
	/**
	 * Redirects the client to the specified URI.
	 * 
	 * The URI will always be transformed into a Europa URI unless 
	 * $europaRelative is set to false. The script will automatically
	 * be terminated after the redirect.
	 * 
	 * @param $uri            The URI to redirect to.
	 * @param $europaRelative Whether or not to automatically transform
	 *                        the passed URI into a Europa URI.
	 * 
	 * @return void
	 */
	protected function redirect($uri = '/', $europaRelative = true)
	{
		if ($europaRelative) {
			$uri = $this->view->uri($uri);
		}
		
		header('Location: ' . $uri);
		
		exit;
	}
}