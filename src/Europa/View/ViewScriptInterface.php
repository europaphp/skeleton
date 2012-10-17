<?php

namespace Europa\View;

/**
 * Allows a script to render files.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ViewScriptInterface
{
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The script to render.
     * 
     * @return ViewScriptInterface
     */
    public function setScript($script);
    
    /**
     * Returns the current script.
     * 
     * @return string
     */
    public function getScript();

    /**
     * Sets the script locator.
     * 
     * @param callable $scriptLocator The locator to use.
     * 
     * @return ViewScriptAbstract
     */
    public function setScriptLocator(callable $locator);

    /**
     * Returns the script locator.
     * 
     * @return callable
     */
    public function getScriptLocator();

    /**
     * Locates the view script using the set locator.
     * 
     * @return string
     */
    public function locateScript();
}