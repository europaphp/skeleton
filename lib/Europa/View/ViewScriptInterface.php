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
interface ViewScriptInterface extends ViewInterface
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
     * Returns the script to be rendered.
     * 
     * @return string
     */
    public function getScript();
    
    /**
     * Renders the specified script without affecting the current set script.
     * 
     * @param string $script  The script to render.
     * @param array  $context The parameters to render with.
     * 
     * @return string
     */
    public function renderScript($script, array $context = array());
}
