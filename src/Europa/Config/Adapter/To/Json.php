<?php

namespace Europa\Config\Adapter\To;
use Europa\Exception\Exception;

class Json
{
    public function __invoke($data)
    {
        return json_encode($data);
    }
}