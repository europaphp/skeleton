<?php

namespace Europa\App;
use ArrayAccess;
use Countable;
use Europa\Di\ServiceContainerInterface;
use IteratorAggregate;

interface ModuleManagerInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Bootstraps all of the modules.
     * 
     * @return ModuleManagerInterface
     */
    public function bootstrap();
}