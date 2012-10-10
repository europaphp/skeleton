<?php

namespace Europa\Lang\Adapter;
use InvalidArgumentException;

class Ini
{
    private $file;

    public function __construct($file)
    {
        if (!$this->file = realpath($file)) {
            throw new InvalidArgumentException(sprintf('The Language Ini adapter requires a valid file be passed. The file "%s" is not valid.', $file));
        }
    }

    public function __invoke()
    {
        return parse_ini_file($this->file);
    }
}