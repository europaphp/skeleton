<?php

namespace Europa\Event;

interface DataInterface
{
    public function __set($name, $value);

    public function __get($name);
}
