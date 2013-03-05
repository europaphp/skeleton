<?php

namespace Europa\Module\Exception;

class InvalidBootstrapperInstance extends \InvalidArgumentException
{
    public function __construct($class)
    {
        parent::__construct(sprintf('The bootstrapper class "%s" must implement "Europa\Module\Bootstrapper\BootstrapperInterface".', $class));
    }
}