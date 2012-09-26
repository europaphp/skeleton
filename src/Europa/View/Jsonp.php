<?php

namespace Europa\View;

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
     * The default JSONP callback.
     * 
     * @var string
     */
    const CALLBACK = 'callback';

    /**
     * The callback function name to wrap the JSON data in.
     * 
     * @var string
     */
    private $callback;

    /**
     * Sets up a new JSONP view.
     * 
     * @param string $callback The callback name.
     * 
     * @return Jsonp
     */
    public function __construct($callback = self::CALLBACK)
    {
        $this->callback = $callback;
    }

    /**
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */    
    public function render(array $context = array())
    {
        return $this->callback . '(' . parent::render($context) . ')';
    }
}