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
abstract class Europa_Controller
{
	/**
	 * The Europa_Request instance that initiated dispatching.
	 * 
	 * @var Europa_Request
	 */
	protected $_request;
	
	/**
	 * The Europa_View instance that represents the layout.
	 * 
	 * @var Europa_View
	 */
	protected $_layout;
	
	/**
	 * The Europa_View instance that represents the view.
	 * 
	 * @var Europa_View
	 */
	protected $_view;
	
	/**
	 * Constructs and sets class defaults.
	 * 
	 * @return AbstractController
	 */
	public function __construct()
	{
		$this->_request = Europa_Request::getActiveInstance();
		$this->_layout  = $this->_request->getLayout();
		$this->_view    = $this->_request->getView();
	}
}