<?php

namespace Europa\Config\Adapter;
use ArrayIterator;
use InvalidArgumentException;

class Ini implements AdapterInterface
{
    private $file;

    public function __construct($file)
    {
        if (!$this->file = realpath($file)) {
            throw new InvalidArgumentException(sprintf('The Config Ini adapter requires a valid file be passed. The file "%s" is not valid.', $file));
        }
    }

    public function __invoke()
    {
        return parse_ini_file($this->file);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->__invoke());
    }
}