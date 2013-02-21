<?php

namespace Europa\Module;
use ArrayAccess;
use Countable;
use Europa\Di\ContainerInterface;
use IteratorAggregate;

interface ManagerInterface extends Countable, IteratorAggregate
{
    public function bootstrap(ContainerInterface $container);

    public function add(ModuleInterface $module);

    public function get($name);

    public function has($name);
}