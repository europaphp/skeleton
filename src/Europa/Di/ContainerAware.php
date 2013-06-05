<?php

namespace Europa\Di;

trait ContainerAware
{
    private $container;

    public function setContainer(callable $container)
    {
        $this->container = $container;
        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }
}