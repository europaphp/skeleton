<?php

namespace Europa\Filter;

class CallbackFilter implements FilterInterface
{
    private $callback;
    
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The value specified for $callback is not callable.');
        }
        $this->callback = $callback;
    }
    
    public function filter($value)
    {
        return call_user_func($this->callback, $value);
    }
}
