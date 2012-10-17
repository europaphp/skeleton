<?php

namespace Europa\View;
use Europa\Config\Config;

/**
 * A view class for rendering JSONP data from bound parameters.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Jsonp extends Json
{
    /**
     * The configuration
     * 
     * @var array | Config
     */
    private $config;

    /**
     * Sets up a new JSONP view.
     * 
     * @param string $callback The callback name.
     * 
     * @return Jsonp
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }

    /**
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */    
    public function __invoke(array $context = array())
    {
        return $this->callback . '(' . parent::render($context) . ')';
    }
}