<?php

namespace Europa\Controller;
use Europa\Di\DependencyInjectorAwareInterface;

interface ControllerInterface
{
    public function __call($action, array $context = []);
}