<?php

namespace Europa\Di\Exception;
use Europa\Di\ContainerInterface;

class UnregisteredService extends \RuntimeException
{
    public function __construct($serviceName, ContainerInterface $container)
    {
        if ($name = $container->name()) {
            $containerMessage = ' in the container "' . $name . '".';
        } else {
            $containerMessage = '. Additionally the container it was requested in was not registered.';
        }

        parent::__construct(sprintf('The service "%s" is not registered%s', $serviceName, $containerMessage));
    }
}