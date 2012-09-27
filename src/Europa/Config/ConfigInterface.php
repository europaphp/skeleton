<?php

namespace Europa\Config;
use ArrayAccess;
use IteratorAggregate;

interface ConfigInterface extends ArrayAccess, IteratorAggregate
{
    public function __set($name, $value);

    public function __get($name);

    public function __isset($name);

    public function __unset($name);

    public function import($config);

    public function export();
}