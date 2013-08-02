<?php

namespace Europa\Module;
use Europa\Config;
use Europa\Filter;

abstract class ModuleAbstract implements ModuleInterface, RouteAwareInterface, ViewScriptAwareInterface
{
  const BOOTSTRAPPER = 'Bootstrapper';

  const VERSION = '0.0.0';

  protected $config = [];

  protected $dependencies = [];

  protected $name;

  protected $namespace;

  protected $path = '../..';

  protected $routes = [];

  protected $viewPaths = [
    ['views', 'php']
  ];

  public function __construct($config = [])
  {
    $this->initNamespace();
    $this->initName();
    $this->initPath();
    $this->initConfig($config);
    $this->initRoutes();
    $this->init();
  }

  public function init()
  {

  }

  public function bootstrap(callable $container)
  {
    $class = $this->namespace . '\\' . static::BOOTSTRAPPER;

    if (class_exists($class)) {
      $class = new $class;

      if (!is_callable($class)) {
        throw new Exception\BootstrapperNotCallable(sprintf(
          'The bootstrapper class "%s" must be callable.',
          get_class($class)
        ));
      }

      $class($this, $container);
    }
  }

  public function ns()
  {
    return $this->namespace;
  }

  public function name()
  {
    return $this->name;
  }

  public function version()
  {
    return static::VERSION;
  }

  public function path()
  {
    return $this->path;
  }

  public function config()
  {
    return $this->config;
  }

  public function dependencies()
  {
    return $this->dependencies;
  }

  public function routes()
  {
    return $this->routes;
  }

  public function viewPaths()
  {
    return $this->viewPaths;
  }

  private function formatNameToNamespace()
  {
    $filter = new Filter\ClassNameFilter;
    return $filter($this->name);
  }

  private function initNamespace()
  {
    if (!$this->namespace) {
      $this->namespace = get_class($this);
    }
  }

  private function initName()
  {
    if (!$this->name) {
      $this->name = $this->namespace;
    }

    $this->name = strtolower($this->name);
    $this->name = str_replace(['\\', '_'], '/', $this->name);
  }

  private function initPath()
  {
    $path = (new \ReflectionClass($this))->getFileName();
    $path = dirname($path);

    if ($this->path) {
      $path .= '/' . $this->path;
    }

    if (!$this->path = realpath($path)) {
      throw new Exception\InvalidPath($this->name, $path);
    }
  }

  private function initConfig($config)
  {
    if (is_string($this->config)) {
      $this->config = $this->path . '/' . $this->config;
    }

    $this->config = new Config\Config($this->config, $config);
  }

  private function initRoutes()
  {
    if (is_string($this->routes)) {
      $this->routes = $this->path . '/' . $this->routes;
    }

    $this->routes = new Config\Config($this->routes);
  }
}