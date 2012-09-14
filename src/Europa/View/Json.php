<?php

namespace Europa\View;
use Europa\View;

/**
 * A view class for rendering JSON data from bound parameters.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Json implements ViewInterface
{
    /**
     * The configuration for the view.
     * 
     * @var array
     */
    private $config = [
        'jsonp' => null
    ];

    /**
     * Sets up the JSON view renderer.
     * 
     * @param string $config The configuration array.
     * 
     * @return Json
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */    
    public function render(array $context = array())
    {
        $render = $this->formatParamsToJsonArray($context);
        $render = json_encode($context);
        return $this->config['jsonp'] ? $this->config['jsonp'] . '(' . $render . ')' : $render;
    }
    
    /**
     * Serializes the passed in parameters into an array.
     *  
     * @return array
     */
    private function formatParamsToJsonArray($data)
    {
        $array = array();
        foreach ($data as $name => $item) {
            if (is_array($item) || is_object($item)) {
                $item = $this->formatParamsToJsonArray($item);
            }
            $array[$name] = $item;
        }
        return $array;
    }
}
