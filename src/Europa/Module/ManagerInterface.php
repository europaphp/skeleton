<?php

namespace Europa\Module;
use ArrayAccess;
use Countable;
use Europa\Di\ServiceContainerInterface;
use IteratorAggregate;

interface ManagerInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function bootstrap();
    public function isBootstrapped();
    public function isModuleBootstrapped($module);
    public function setServiceContainer(ServiceContainerInterface $container);
    public function getServiceContainer();
}