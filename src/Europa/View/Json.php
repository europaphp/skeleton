<?php

namespace Europa\View;

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
     * JSON encodes the parameters on the view and returns them.
     * 
     * @return string
     */    
    public function render(array $context = array())
    {
        $render = $this->formatParamsToJsonArray($context);
        $render = json_encode($context);
        return $render;
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