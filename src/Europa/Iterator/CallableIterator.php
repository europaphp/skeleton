<?php

namespace Europa\Iterator;
use Europa\Exception\Exception;

class CallableIterator implements \IteratorAggregate
{
    private $callables;

    public function __construct(\Traversable $callables)
    {
        foreach ($callables as $callable) {
            if (!is_callable($callable)) {
                Exception::toss('The item at offset "%s" must be callable. Type of "%s" supplied.', $this->key(), gettype($callable));
            }
        }

        $this->callables = $callables;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->callables);
    }
}