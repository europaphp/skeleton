<?php

namespace Europa\Controller;

/**
 * The most basic controller implementation. All it needs to do is specify an action method.
 *
 * @category Controller
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ControllerInterface
{
    /**
     * Performs actioning and returns the view context.
     * 
     * @return array
     */
    public function action();
    
    /**
     * Renders the view and returns the result.
     * 
     * @return string
     */
    public function render();
}