<?php

namespace Europa\App;

interface ModuleConfigurationInterface
{
    /**
     * Returns the application configuration object.
     * 
     * @param mixed $defaults The default configuration.
     * @param mixed $config   The configuration to use.
     * 
     * @return Config
     */
    public function config($defaults, $config);

    /**
     * Returns the locator responsible for finding class files.
     * 
     * @return Europa\Fs\Locator
     */
    public function loaderLocator();

    /**
     * Returns the router that routes the request to a controller.
     * 
     * @return Europa\Router\Router
     */
    public function router();

    /**
     * Returns the locator responsible for finding view scripts.
     * 
     * @return Europa\Fs\Locator
     */
    public function viewLocator();
}