<?php

namespace Europa\Di\Exception;

class UndefinedDependency extends \RuntimeException
{
    public function __construct($name, $dependency)
    {
        parent::__construct(sprintf('The service "%s" requires the service "%s" be defined.', $name, $dependency));
    }
}