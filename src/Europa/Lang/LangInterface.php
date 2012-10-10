<?php

namespace Europa\Lang;
use ArrayAccess;
use Countable;
use IteratorAggregate;

interface LangInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function __call($name, array $args = []);

    public function __set($name, $value);

    public function __get($name);

    public function __isset($name);
}