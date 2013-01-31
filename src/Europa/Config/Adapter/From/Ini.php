<?php

namespace Europa\Config\Adapte\From;

class Ini
{
    public function __invoke($data)
    {
        return parse_ini_string($data);
    }
}