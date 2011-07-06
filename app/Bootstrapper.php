<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../lib/Europa/Bootstrapper.php';

use Europa\Bootstrapper as ParentBootstrapper;
use Europa\ClassLoader;
use Europa\Di\Container;
use Europa\Fs\Directory;

/**
 * Bootstraps the sample application.
 * 
 * @category Bootstrapping
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Bootstrapper extends ParentBootstrapper
{
    /**
     * The dependency injection container.
     * 
     * @var \Europa\Di\Container
     */
    private $container;
    
    /**
     * Sets default options.
     * 
     * @return Bootstrapper
     */
    public function __construct()
    {
        $this->setOptions(array(
            'basePath'   => realpath(dirname(__FILE__) . '/../'),
            'pluginPath' => realpath(dirname(__FILE__) . '/../plugins')
        ));
    }
    
    /**
     * Adds load paths to the loader for autoloading.
     * 
     * @return void
     */
    public function setUpLibraryLoading()
    {
        require $this->basePath . '/lib/Europa/ClassLoader.php';
        $loader = new ClassLoader;
        $loader->register();
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @return void
     */
    public function configureDiContainer()
    {
        $this->container = Container::get()->map(array(
            'defaultRoute'           => '\Europa\Router\Route\Regex',
            'finder'                 => '\Europa\Fs\Finder',
            'langLocator'            => '\Europa\Fs\Locator',
            'loader'                 => '\Europa\ClassLoader',
            'loaderLocator'          => '\Europa\Fs\Locator',
            'phpView'                => '\Europa\View\Php',
            'phpViewHelperContainer' => '\Europa\Di\Container',
            'phpViewLocator'         => '\Europa\Fs\Locator',
            'request'                => '\Europa\Request\Http',
            'router'                 => '\Europa\Router\Basic',
            'response'               => '\Europa\Response',
            'dispatcher'             => '\Europa\Dispatcher'
            
        ));
    }
    
    /**
     * Configures the DI Container's dependencies.
     * 
     * @return void
     */
    public function configureDiDependencies()
    {
        $this->container->defaultRoute('(index\.php)?/?(?<controller>.+)?', null, array('controller' => 'index'));
        $this->container->langLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app/Lang', 'ini');
        $this->container->loader
            ->setLocator($this->container->loaderLocator);
        $this->container->loaderLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app');
        $this->container->phpView($this->container->phpViewLocator)
            ->setHelperContainer($this->container->phpViewHelperContainer);
        $this->container->phpViewHelperContainer
            ->setFormatter(function($name) {
                $name = ucfirst($name);
                return "\\Helper\\{$name}";
            })
            ->css('css')
            ->js('js')
            ->lang($this->container->langLocator, $this->container->phpView, 'en-us');
        $this->container->phpViewLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app/View');
        $this->container->router
            ->setRoute($this->container->defaultRoute);
    }
    
    /**
     * Bootstraps the plugins.
     * 
     * @return void
     */
    public function bootstrapPlugins()
    {
        $finder = $this->container->finder->create();
        $finder->in($this->pluginPath);
        $finder->depth(0);
        foreach ($finder as $item) {
            $moduleBasePath = $item->getPathname() . DIRECTORY_SEPARATOR;
            $this->container->langLocator->addPath($moduleBasePath . 'app/Lang', 'ini');
            $this->container->loaderLocator->addPath($moduleBasePath . 'app');
            $this->container->loaderLocator->addPath($moduleBasePath . 'lib');
            $this->container->phpViewLocator->addPath($moduleBasePath . 'app/View');
        }
    }
    
    /**
     * This must be done last as it will auto-configure it's dependencies.
     * 
     * @return void
     */
    public function registerAutoloadingForEverythingElse()
    {
        $this->container->loader->get()->register();
    }
}