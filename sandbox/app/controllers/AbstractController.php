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
abstract class AbstractController extends Europa_Controller
{
	/**
	 * Constructs defaults and forces the index layout unless otherwise
	 * specified.
	 * 
	 * @return AbstractController
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_layout->setScript('Index');
	}
	
	/**
	 * Traps any undefined action calls. In this case, it dispatches to the
	 * error controller and exists after it.
	 * 
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function __call($name, $args)
	{
		$europa = new Europa_Request_Http;
		$europa->setParam('controller', 'error')
		       ->setParam('action', 'notFound');
		echo $europa->dispatch()->toString();
		exit;
	}
}