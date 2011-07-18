<?php

namnespace Helper;
use Europa\View\ViewInterface;

/**
 * Creates and renders a partial representing the specified view file.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Partial
{
    /**
     * The view instance to use for rendering partials.
     * 
     * @var ViewInterface
     */
    private $view;
    
    /**
     * Sets up the view helper.
     * 
     * @param ViewInterface $view The view file to use for rendering partials.
     * 
     * @return Partial
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }
    
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
        // get the old script so we can re-set it and get the new script output
        $old = $this->view->getScript();
        $new = $this->view->setScript($script)->render($context);
        
        // re-set to old script
        $this->view->setScript($old);
        
        // after reset, return the new script output
        return $new;
    }
}
