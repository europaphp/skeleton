<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Config\Adapter\Ini as ConfigIni;
use Europa\Fs\Locator\Locator;
use Europa\Lang\Lang;
use Europa\Router\Adapter\Ini as RouterIni;
use Europa\Router\Router;
use UnexpectedValueException;

class Module implements ModuleInterface
{
    private $internalConfig = [
        'bootstrap' => 'bootstrap.php',
        'config'    => 'configs/config.ini',
        'routes'    => 'configs/routes.ini',
        'classes'   => [
            'classes' => 'php'
        ],
        'langs' => [
            'langs/en-us' => 'ini'
        ],
        'views' => [
            'views/cli' => 'php',
            'views/web' => 'php'
        ]
    ];

    private $name;

    private $path;

    private $config;

    private $classLocator;

    private $langLocator;

    private $routes = [];

    private $viewLocator;

    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" does not exist.', $path));
        }

        $this->name = basename($this->path);

        $this->internalConfig = new Config($this->internalConfig, $config);

        $this->initConfig();
        $this->initClassLocator();
        $this->initLangLocator();
        $this->initRoutes();
        $this->initViewLocator();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getClassLocator()
    {
        return $this->classLocator;
    }

    public function getInternalConfig()
    {
        return $this->internalConfig;
    }

    public function getLangLocator()
    {
        return $this->langLocator;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getViewLocator()
    {
        return $this->viewLocator;
    }

    public function bootstrap()
    {
        if (file_exists($path = $this->path . '/' . $this->internalConfig->bootstrap)) {
            require_once $path;
        }

        return $this;
    }

    private function initConfig()
    {
        $this->config = new Config;

        if (is_file($path = $this->path . '/' . $this->internalConfig->config)) {
            $this->config->import(new ConfigIni($path));
        }
    }

    private function initClassLocator()
    {
        $this->classLocator = new Locator;
        $this->classLocator->setBasePath($this->path);
        $this->classLocator->addPaths($this->internalConfig->classes);
    }

    private function initLangLocator()
    {
        $this->langLocator = new Locator;
        $this->langLocator->setBasePath($this->path);
        $this->langLocator->addPaths($this->internalConfig->langs);
    }

    private function initRoutes()
    {
        if (is_file($path = $this->path . '/' . $this->internalConfig->routes)) {
            $this->routes = new RouterIni($path);
        }
    }

    private function initViewLocator()
    {
        $this->viewLocator = new Locator;
        $this->viewLocator->setBasePath($this->path);
        $this->viewLocator->addPaths($this->internalConfig->views);
    }
}