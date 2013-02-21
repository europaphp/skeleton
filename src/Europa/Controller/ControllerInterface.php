<?php

namespace Europa\Controller;
use Europa\Di\DependencyInjectorAwareInterface;

interface ControllerInterface extends DependencyInjectorAwareInterface
{
    public function __call($action, array $context = []);
}