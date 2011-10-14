<?php

namespace Europa\View;

/**
 * Most basic view implementation.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ViewInterface
{
    /**
     * Renders the specified view using the specified context.
     * 
     * @param array $context The context to render.
     * 
     * @return string
     */
    public function render(array $context = array());
}
