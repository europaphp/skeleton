<?php

use Europa\Controller;
use Europa\Controller\FilterInterface;
use Europa\String;

/**
 * Authorization filter for filtering an unathoried user.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ParamFilter implements FilterInterface
{
	/**
	 * Filteres the specified controller.
	 * 
	 * @param \Europa\Controller $controller The controller to filter.
	 * 
	 * @return void
	 */
    public function filter(Controller $controller)
    {
    	$request = $controller->getRequest();
        foreach ($request->getParams() as $name => $value) {
        	$request->setParam($name, String::create($value)->cast());
        }
    }
}