<?php

namespace Controller;
use Europa\Controller;
use Europa\ServiceLocator;

/**
 * An example of controller abstraction that sets up a default view scheme.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Base extends Controller
{
    /**
     * Sets up the a default view scheme.
     * 
     * @return void
     */
    public function init()
    {
        $view = ServiceLocator::getInstance()->get('phpView');
        $view->setScript(get_class($this));
        $this->setView($view);
    }
}