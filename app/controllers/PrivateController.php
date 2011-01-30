<?php

/**
 * An example controller showing the application of behaviors.
 * 
 * @preFilter AuthFilter
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class PrivateController extends AbstractController
{
	/**
	 * Demo adding an authorization behavior to a get method. The authorization
	 * was imposed on all method by adding to the class block as a @preFilter
	 * tag.
	 * 
	 * @param test $test test description multiline
	 *                        multiline one
	 *                        multiline two
	 * 
	 * @return void
	 */
	public function get()
	{
		
	}
}