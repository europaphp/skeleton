<?php

namespace Europa\Controller;

/**
 * Defines a basic filter implementation for controllers.
 *
 * @category Controller
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface FilterInterface
{
    /**
     * Filters the specified controller. The method is responsible for taking over and doing any further routing if it
     * is necessary.
     * 
     * @return void
     */
    public function filter(ControllerInterface $controller);
}
