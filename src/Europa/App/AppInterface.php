<?php

namespace Europa\App;
use ArrayAccess;
use Countable;
use IteratorAggregate;
use Europa\Di\ServiceContainerInterface;

interface AppInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function __invoke();

    public function setServiceContainer(ServiceContainerInterface $container);

    public function getServiceContainer();
}