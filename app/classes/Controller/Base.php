<?php

namespace Controller;
use Europa\Di\Container;
use Europa\Controller\RestController;

/**
 * Base controller that sets up all controllers.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright(c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Base extends RestController
{
    /**
     * Set up controllers.
     * 
     * @return void
     */
    public function init()
    {
        $view = Container::get()->view;
        $view->setScript(get_class($this));
        $this->setView($view);
    }
}