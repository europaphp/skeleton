<?php

namnespace Helper;
use Europa\ServiceLocator;
use Europa\View\Php;

/**
 * Creates and renders a partial representing the specified view file.
 * 
 * @category ViewHelpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Partial
{
    /**
     * Returns the dispatch result as a string.
     * 
     * @param string $script  The path to the script to render.
     * @param array  $context An array of parameters to pass off to the new view.
     * 
     * @return string
     */
    public function render($script, array $context = array())
    {
        $view = Container::get()->create('phpView');
        return $view->setScript($script)->render($context);
    }
}