<?php

namespace Europa\App;
use Europa\Di\ServiceContainerInterface;

interface AppInterface
{
    public function __invoke();

    public function setServiceContainer(ServiceContainerInterface $container);

    public function getServiceContainer();
}