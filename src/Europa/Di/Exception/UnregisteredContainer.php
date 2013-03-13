<?php

namespace Europa\Di\Exception;

class UnregisteredContainer extends \RuntimeException
{
    public function __construct($instanceName)
    {
        parent::__construct(sprintf('The container "%s" is not registered.', $instanceName));
    }
}