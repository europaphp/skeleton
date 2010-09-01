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
abstract class Europa_Controller_Action extends Europa_Controller
{
	/**
	 * The layout that will be rendered.
	 * 
	 * @var Europa_View
	 */
	protected $_layout;
	
	/**
	 * The view that will be rendered.
	 * 
	 * @var Europa_View
	 */
	protected $_view;
	
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
		// by default, an action must exist
		throw new Europa_Controller_Exception(
			"Action {$action}() does not exist.",
			Europa_Controller_Exception::ACTION_NOT_FOUND
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
		$this->_layout = new Europa_View_Php($this->_formatLayout());
		$this->_view   = new Europa_View_Php($this->_formatView());
		
		// call the init and handle the return value
		$params = $this->_init();
		if ($params === false) {
			$this->_layout = null;
		} elseif ($this->_layout) {
			$this->_layout->setParams($params);
		}

		// get action parameters
		$params = array();
		$action = $this->_formatAction();
		if (method_exists($this, $action)) {
			$params = $this->_getMappedParams($action);
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
	protected function _init()
	{
		
	}

	/**
	 * Formats the action and returns it.
	 * 
	 * @return string
	 */
	protected function _formatAction()
	{
		$action = $this->getRequest()->getParam('action', 'index');
		return Europa_String::create($action)->toClass()->__toString() . 'Action';
	}

	/**
	 * Formats the layout and returns it.
	 * 
	 * @return string
	 */
	protected function _formatLayout()
	{
		$layout = Europa_String::create($this->getRequest()->getController());
		return $layout->toClass() . 'View';
	}

	/**
	 * Formats the view and returns it.
	 * 
	 * @return string
	 */
	protected function _formatView()
	{
		$layout = Europa_String::create($this->getRequest()->getController());
		$view   = Europa_String::create($this->getRequest()->getParam('action', 'index'));
		return $layout->toClass() . '/' . $view->toClass() . 'View';
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
}