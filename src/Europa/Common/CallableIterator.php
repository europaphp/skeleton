<?php

namespace Europa\Common;
use Europa\Exception\Exception;
use Iterator;
use Traversable;

class CallableIterator implements Iterator
{
    private $callables;

    public function __construct(Traversable $callables)
    {
        $this->callables  = $callables;
    }

    public function current()
    {
        $callable = current($this->callables);

        if (!is_callable($callable)) {
            Exception::toss('The item at offset "%s" must be callable. Type of "%s" supplied.', $this->key(), gettype($instance));
        }

        return $callable;
    }

    public function key()
    {
        return key($this->callables);
    }

    public function next()
    {
        next($this->callables);
    }

    public function rewind()
    {
        reset($this->callables);
    }

    public function valid()
    {
        return $this->key() !== null;
    }
}