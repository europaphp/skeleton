<?php

namespace Europa\Module;
use Europa\Config\Config;
use Europa\Config\Adapter\Ini;
use Europa\Config\ConfigInterface;
use Europa\Fs\Locator\Locator;
use Europa\Lang\Lang;
use UnexpectedValueException;

class Module implements ModuleInterface
{
    private $config = [
        'bootstrap' => 'bootstrap.php',
        'classes'   => 'classes',
        'config'    => 'configs/config.ini',
        'langs'     => 'langs/en-us',
        'views'     => 'views'
    ];

    private $name;

    private $path;

    private $moduleConfig;

    private $classLocator;

    private $langLocator;

    private $viewLocator;

    public function __construct($path, $config = [])
    {
        if (!$this->path = realpath($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" does not exist.', $path));
        }

        $this->name   = basename($this->path);
        $this->config = new Config($this->config, $config);

        $this->initConfig();
        $this->initClassLocator();
        $this->initLangLocator();
        $this->initViewLocator();
    }

    public function name()
    {
        return $this->name;
    }

    public function path()
    {
        return $this->path;
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setClassLocator(callable $locator)
    {
        $this->classLocator = $locator;
        return $this;
    }

    public function getClassLocator()
    {
        return $this->classLocator;
    }

    public function setLangLocator(callable $locator)
    {
        $this->langLocator = $locator;
        return $this;
    }

    public function getLangLocator()
    {
        return $this->langLocator;
    }

    public function setViewLocator(callable $locator)
    {
        $this->viewLocator = $locator;
        return $this;
    }

    public function getViewLocator()
    {
        return $this->viewLocator;
    }

    public function bootstrap()
    {
        if (file_exists($path = $this->path . '/' . $this->config->bootstrap)) {
            require_once $path;
        }

        return $this;
    }

    private function initConfig()
    {
        if (is_file($path = $this->path . '/' . $this->config->config)) {
            $this->moduleConfig = new Config(new Ini($path));
        }
    }

    private function initClassLocator()
    {
        $this->classLocator = new Locator;
        $this->classLocator->setBasePath($this->path . '/' . $this->config->classes, true);
    }

    private function initLangLocator()
    {
        $this->langLocator = new Locator;
        $this->langLocator->setBasePath($this->path . '/' . $this->config->langs);
    }

    private function initViewLocator()
    {
        $this->viewLocator = new Locator;
        $this->viewLocator->setBasePath($this->path . '/' . $this->config->views);
    }
}