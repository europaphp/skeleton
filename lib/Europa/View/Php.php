<?php

namespace Europa\View;

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden to provide base functionality for view
 * manipulation while the __toString method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends ViewScriptAbstract
{
    /**
     * Executes the currently set script.
     * 
     * @return string
     */
    public function execute()
    {
        ob_start();
        include $this->getScriptPathname();
        return ob_get_clean();
    }
}
