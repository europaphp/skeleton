<?php

namespace Europa\View;

class Json
{
    public function __invoke(array $context = array())
    {
        $render = $this->formatParamsToJsonArray($context);
        $render = json_encode($context);
        return $render;
    }
    
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