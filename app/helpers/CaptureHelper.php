<?php

use Europa\View;

/**
 * Designed to capture and save in-view data for displaying later in the view.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class CaptureHelper
{
    /**
     * The view that called the helper.
     * 
     * @var \Europa\View
     */
    private $view;
    
    /**
     * The captured data.
     * 
     * @var array
     */
    private $cache = array();
    
    /**
     * Constructs the new capture helper and sets the view that called it.
     * 
     * @param \Europa\View $view The view that called the helper.
     * 
     * @return \CaptureHelper
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }
    
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