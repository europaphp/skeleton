<?php

namespace Europa\Config;
use ArrayAccess;
use Countable;
use Iterator;
use Serializable;

interface ConfigInterface extends ArrayAccess, Countable, Iterator, Serializable
{
    public function __set($name, $value);

    public function __get($name);

    public function __isset($name);

    public function __unset($name);

    public function import($config);

    public function export();

    public function setParent(ConfigInterface $config);

    public function getParent();

    public function getRootParent();
}