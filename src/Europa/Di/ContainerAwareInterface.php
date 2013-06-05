<?php

namespace Europa\Di;

interface ContainerAwareInterface
{
    public function setContainer(callable $container);

    public function getContainer();
}