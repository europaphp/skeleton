<?php

abstract class Europa_Controller_Abstract
{
	public
		$dispatcher,
		$route,
		$layout,
		$view;
	
	public function __construct()
	{
		$this->dispatcher = Europa_Dispatcher::getActiveInstance();
		$this->route      = $this->dispatcher->getRoute();
		$this->layout     = $this->dispatcher->getLayout();
		$this->view       = $this->dispatcher->getView();
	}
	
	protected function _forward($action)
	{
		// force a route
		$this->dispatcher->setRoute(new Europa_Route(array(
			'controller' => $this->route->getParam('controller'),
			'action'     => $action
		)));
		
		// by dispatching here, it cancels the last dispatch call
		$this->dispatcher->dispatch();
		
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
	protected function _redirect($uri = '/', $europaRelative = true)
	{
		if ($europaRelative) {
			$uri = $this->view->uri($uri);
		}
		
		header('Location: ' . $uri);
		
		exit;
	}
}