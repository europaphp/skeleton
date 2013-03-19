<?php

namespace Europa\Common;
use Europa\Exception\Exception;
use Iterator;
use Traversable;

class InstanceIterator implements Iterator
{
    private $instanceof;

    private $instances;

    public function __construct($instanceof, Traversable $instances)
    {
        $this->instanceof = $instanceof;
        $this->instances  = $instances;
    }

    public function current()
    {
        $instance = current($this->instances);

        if (!is_object($instance)) {
            Exception::toss('The item at offset "%s" must be an object instance. Type of "%s" supplied.', $this->key(), gettype($instance));
        }

        if (!$instance instanceof $this->instanceof) {
            Exception::toss('The instance at offset "%s" must be an instance of "%s". Instance of "%s" supplied.', $this->key(), $this->instanceof, get_class($instance));
        }

        return $instance;
    }

    public function key()
    {
        return key($this->instances);
    }

    public function next()
    {
        next($this->instances);
    }

    public function rewind()
    {
        reset($this->instances);
    }

    public function valid()
    {
        return $this->key() !== null;
    }
}