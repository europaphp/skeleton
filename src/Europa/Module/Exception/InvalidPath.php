<?php

namespace Europa\Module\Exception;

class InvalidPath extends \InvalidArgumentException
{
    public function __construct($name, $path)
    {
        parent::__consruct(sprintf('The module "%s" specified and invalid path "%s".', $name, $path));
    }
}