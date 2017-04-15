<?php

namespace Europa\Di\Exception;

class ContainerAlreadyRegistered extends \RuntimeException
{
    public function __construct($instanceName)
    {
        parent::__construct(sprintf('The container "%s" cannot overwrite an existing container that has been saved with the same name.', $instanceName));
    }
}