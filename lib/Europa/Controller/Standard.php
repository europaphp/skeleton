<?php

/**
 * A standard controller base class that implements a layout view system in
 * a one-controller-per-action environment.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller_Standard extends Europa_Controller
{
	/**
	 * The view rendering the page.
	 * 
	 * @var Europa_View
	 */
	private $_view;
	
	/**
	 * Constructs the controller and sets the request to use.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller_Standard
	 */
	public function __construct($request)
	{
		// make sure defaults are set from parent
		parent::__construct($request);
		
		// map properties
		$this->_mapRequestToProperties();
		
		// format the controller
		$controller = $request->getController();
		$controller = Europa_String::create($controller)->toClass()->replace('_', DIRECTORY_SEPARATOR);
		
		// set views
		$this->_view = new Europa_View_Layout;
		$this->_view->setLayout(new Europa_View_Php('IndexLayout'));
		$this->_view->setView(new Europa_View_Php($controller . 'View'));
	}
	
	/**
	 * Renders the view.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		$this->preRender();
		$view = $this->_view->__toString();
		$this->postRender();
		return $view;
	}
	
	/**
	 * Sets the view to use.
	 * 
	 * @param Europa_View $view The view to use.
	 * @return Europa_Controller_Standard
	 */
	public function setView(Europa_View $view = null)
	{
		$this->_view = $view;
		return $this;
	}
	
	/**
	 * Returns the view being used.
	 * 
	 * @return Europa_View
	 */
	public function getView()
	{
		return $this->_view;
	}
	
	/**
	 * Sets properties from the request onto the class. If a property exists that
	 * doesn't have a default value and it doesn't exist in the request, then an
	 * exception is thrown.
	 * 
	 * @return void
	 */
	protected function _mapRequestToProperties()
	{
		$params = $this->getRequest()->getParams();
		foreach ($params as &$param) {
			$param = strtolower($param);
		}
		
		$class = new ReflectionClass($this);
		foreach ($class->getProperties() as $property) {
			$normalcase = $property->getName();
			$lowercase  = strtolower($normalcase);
			if (isset($params[$lowercase])) {
				$this->$normalcase = $params[$lowercase];
			} elseif (!isset($this->$normalcase)) {
				throw new Europa_Controller_Exception(
					"Required request parameter {$normalcase} was not defined."
				);
			}
			
			// cast the parameter if it is scalar
			if (is_scalar($this->$normalcase)) {
				$this->$normalcase = Europa_String::create($this->$normalcase)->cast();
			}
		}
	}
}