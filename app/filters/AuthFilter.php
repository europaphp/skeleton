<?php

use Europa\Controller;
use Europa\Controller\FilterInterface;

/**
 * Authorization filter for filtering an unathorized user.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class AuthFilter implements FilterInterface
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
        if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
            $controller->redirect('index.php/log-in');
        }
    }
}