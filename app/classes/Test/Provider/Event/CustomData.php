<?php

namespace Test\Provider\Event;
use Europa\Event\DataInterface;

class CustomData implements DataInterface
{
    private $data = array(
        'customData' => true
    );

    public function __set($name, $value)
    {
        // readonly
    }

    public function __get($name)
    {
        return $this->data[$name];
    }
}
