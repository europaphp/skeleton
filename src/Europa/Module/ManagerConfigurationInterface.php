<?php

namespace Europa\Module;

interface ManagerConfigurationInterface
{
    public function config($defaults, $config);

    public function loaderLocator();

    public function router();

    public function viewLocator();
}