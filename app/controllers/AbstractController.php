<?php

use Europa\ServiceLocator;

/**
 * An example of controller abstraction that sets up a default view scheme.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class AbstractController extends \Europa\Controller
{
    /**
     * Sets up the a default view scheme.
     * 
     * @return void
     */
    public function init()
    {
        $locator = ServiceLocator::getInstance();
        $locator->get('layout')->setScript('DefaultLayout');
        $locator->get('view')->setScript(str_replace('Controller', 'View', get_class($this)));
        $this->setView($locator->get('layout'));
    }
}