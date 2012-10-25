<?php

namespace Europa\Config\Adapter;
use Europa\Exception\Exception;

class Ini
{
    private $file;

    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The INI config file "%s" does not exist.', $file);
        }
    }

    public function __invoke()
    {
        return parse_ini_file($this->file);
    }
}