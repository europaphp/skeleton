<?php

/**
 * An example controller showing the application of behaviors.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class PrivateController extends \Europa\Controller
{
	/**
	 * Demo adding an authorization behavior to a get method.
	 * 
	 * @behavior AuthBehavior
	 * 
	 * @return void
	 */
	public function get()
	{
		
	}

	/**
	 * Demo adding an authorization behavior to a post method.
	 * 
	 * @behavior AuthBehavior
	 * @behavior CastParamBehavior
	 * 
	 * @return void
	 */
	public function post($id)
	{
    	die(var_dump($id));
	}
}