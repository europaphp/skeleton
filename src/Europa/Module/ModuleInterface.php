<?php

namespace Europa\Module;
use Europa\Di\ContainerInterface;

interface ModuleInterface
{
    public function bootstrap(ContainerInterface $container);

    public function getNamespace();

    public function getName();

    public function getVersion();

    public function getPath();

    public function getConfig();

    public function getDependencies();
}