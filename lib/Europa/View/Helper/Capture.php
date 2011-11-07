<?php

namespace Europa\View\Helper;
use Europa\View\Php;

/**
 * Designed to capture and save in-view data for displaying later in the view.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Capture
{
    /**
     * The captured data.
     * 
     * @var array
     */
    private $cache = array();
    
    /**
     * Starts capturing.
     * 
     * @return \CaptureHelper
     */
    public function start()
    {
        ob_start();
        return $this;
    }
    
    /**
     * Stops capturing and saves the captured data using the specified name.
     * 
     * @param string $name The name to save the captured data as.
     * 
     * @return \CaptureHelper
     */
    public function end($name)
    {
        $this->cache[$name] = ob_get_clean();
        return $this;
    }
    
    /**
     * Returns the captured data that was captured using the specified name.
     * 
     * @param string $name The data to return that was saved using this name.
     * 
     * @return string
     */
    public function get($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        return '';
    }
}