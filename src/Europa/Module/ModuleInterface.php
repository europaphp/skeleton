<?php

namespace Europa\Module;
use Europa\Di\ContainerInterface;

interface ModuleInterface
{
    public function bootstrap(ContainerInterface $container);

    public function getConfig();

    public function getDependencies();

    public function getName();

    public function getPath();

    public function getVersion();
}