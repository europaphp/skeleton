<?php

namespace Europa\Module;
use ArrayAccess;
use Countable;
use Europa\Di\ServiceContainerInterface;
use IteratorAggregate;

interface ManagerInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function bootstrap();
}