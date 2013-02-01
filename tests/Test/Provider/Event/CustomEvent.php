<?php

namespace Test\Provider\Event;
use stdClass;

class CustomEvent
{
    public function __invoke(stdClass $data)
    {
        $data->called = true;
    }
}