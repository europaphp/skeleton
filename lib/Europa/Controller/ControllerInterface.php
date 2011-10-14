<?php

namespace Europa\Controller;

/**
 * Defines a basic implementation of controllers in Europa.
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
