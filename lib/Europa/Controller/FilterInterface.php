<?php

namespace Europa\Controller;

/**
 * The most basic filter implementation allowed for controllers.
 *
 * @category Controller
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface FilterInterface
{
    /**
     * Filters the specified controller.
     * 
     * @return void
     */
    public function filter(ControllerInterface $controller);
}