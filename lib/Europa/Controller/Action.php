<?php

/**
 * An example of an abstract controller to act as a base class for all
 * controllers.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller_Action extends Europa_Controller_Basic
{
	/**
	 * The layout that will be rendered.
	 * 
	 * @var Europa_View
	 */
	private $_layout;
	
	/**
	 * The view that will be rendered.
	 * 
	 * @var Europa_View
	 */
	private $_view;
	
	/**
	 * Extends the layout with the view and returns the layout as a string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (!$this->_view) {
			return '';
		}
		if (!$this->_layout) {
			return $this->_view->__toString();
		}
		return $this->_view->extend($this->_layout)->__toString();
	}
	
	/**
	 * Throws an exception if an action doesn't exist.
	 * 
	 * @param string $action The action that was called.
	 * @param array $args The arguments passed to the action.
	 * @return void
	 */
	public function __call($action, $args)
	{
		throw new Europa_Exception(
			"Action {$action}() does not exist."
		);
	}
	
	/**
	 * Implements action dispatching.
	 * 
	 * @return void
	 */
	public function action()
	{
		// default view scheme
		$this->_layout = new Europa_View_Php($this->_getDefaultLayoutScript());
		$this->_view   = new Europa_View_Php($this->_getDefaultViewScript());
		
		// call the init and handle the return value
		$params = $this->init();
		if ($params === false) {
			$this->_layout = null;
		} elseif ($this->_layout) {
			$this->_layout->setParams($params);
		}

		// call the action
		$params = array();
		$action = $this->_getActionMethod();
		if (method_exists($this, $action)) {
			$params = $this->_getRequest()->getParamsForMethod(
				new ReflectionMethod($this, $action)
			);
		}

		// call the action with it's parameters and handle the return value
		$params = call_user_func_array(array($this, $action), $params);
		if ($params === false) {
			$this->_view = null;
		} elseif ($this->_view) {
			$this->_view->setParams($params);
		}
	}
	
	/**
	 * Initializes the controller. If it returns false, the layout is disabled.
	 * If an array or object is returned, it is iterated over and applied to
	 * the layout.
	 * 
	 * @return mixed
	 */
	public function init()
	{
		
	}
	
	/**
	 * Sets the view.
	 * 
	 * @param Europa_View $view
	 * @return AbstractController
	 */
	protected function _setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Sets the layout.
	 * 
	 * @param Europa_View $layout
	 * @return AbstractController
	 */
	protected function _setLayout(Europa_View $layout = null)
	{
		$this->_layout = $layout;
		return $this;
	}
	
	/**
	 * Returns the action method that should be called.
	 * 
	 * @return string
	 */
	protected function _getActionMethod()
	{
		$action = $this->_getRequest()->getParam('action', 'index');
		$action = Europa_String::create($action)->toClass()->__toString();
		return $action . 'Action';
	}
	
	/**
	 * Returns the layout script that should be set by default.
	 * 
	 * @return string
	 */
	protected function _getDefaultLayoutScript()
	{
		$controller = $this->_getRequest()->getController();
		$controller = Europa_String::create($controller)->toClass()->__toString();
		return $controller . 'View';
	}
	
	/**
	 * Returns the view script that should be set by default.
	 * 
	 * @return string
	 */
	protected function _getDefaultViewScript()
	{
		$controller = $this->_getRequest()->getController();
		$controller = Europa_String::create($controller)->toClass()->__toString();
		$action     = $this->_getRequest()->getParam('action', 'index');
		$action     = Europa_String::create($action)->toClass()->__toString();
		return "{$controller}/{$action}View";
	}
}