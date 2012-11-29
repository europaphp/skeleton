<?php

namespace Europa\Module;
use ArrayAccess;
use Countable;
use Europa\Di\ServiceContainerInterface;
use IteratorAggregate;

interface ManagerInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Bootstraps all of the modules.
     * 
     * @return ModuleManagerInterface
     */
    public function bootstrap();
}