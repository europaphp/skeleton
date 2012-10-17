<?php

namespace Europa\App;

interface ModuleInterface
{
    public function getName();

    public function getConfig();

    public function getRoutes();

    public function getClassPaths();

    public function getViewPaths();

    public function getBootstrapper();
}