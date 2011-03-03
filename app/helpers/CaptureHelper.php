<?php

class CaptureHelper
{
    private $view;
    
    private $cache = array();
    
    public function __construct(\Europa\View $view)
    {
        $this->view = $view;
    }
    
    public function start()
    {
        ob_start();
        return $this;
    }
    
    public function end($name)
    {
        $this->cache[$name] = ob_get_clean();
        return $this;
    }
    
    public function get($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        return '';
    }
}