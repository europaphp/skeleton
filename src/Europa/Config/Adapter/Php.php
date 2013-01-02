<?php

namespace Europa\Router\Adapter;
use Europa\Exception\Exception;

class Php
{
    private $file;

    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The PHP configuration file "%s" does not exist.', $file);
        }
    }

    public function __invoke()
    {
        return include $this->file;
    }
}