<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../lib/Europa/Bootstrapper.php';

use Europa\Bootstrapper as ParentBootstrapper;
use Europa\Di\Container;
use Europa\Fs\Directory;
use Europa\Fs\Loader;

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
        require $this->basePath . '/lib/Europa/Fs/Loader.php';
        $loader = new Loader;
        $loader->register();
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @return void
     */
    public function configureDiContainer()
    {
        $this->container = Container::get();
        $this->container->map(array(
            'defaultRoute'           => '\Europa\Router\Route\Regex',
            'langLocator'            => '\Europa\Fs\Locator',
            'loader'                 => '\Europa\Fs\Loader',
            'loaderLocator'          => '\Europa\Fs\Locator',
            'phpView'                => '\Europa\View\Php',
            'phpViewHelperContainer' => '\Europa\Di\Container',
            'phpViewLocator'         => '\Europa\Fs\Locator',
            'request'                => '\Europa\Request\Http',
            'router'                 => '\Europa\Router\Basic'
        ));
    }
    
    /**
     * Configures the DI Container's dependencies.
     * 
     * @return void
     */
    public function configureDiDependencies()
    {
        $this->container->defaultRoute
            ->configure(array('(index\.php)?/?(?<controller>.+)?', null, array('controller' => 'index')));
        $this->container->langLocator
            ->queue('addPath', array($this->basePath . '/app/Lang', 'ini'));
        $this->container->loader
            ->queue('setLocator', array($this->container->loaderLocator));
        $this->container->loaderLocator
            ->queue('addPath', array($this->basePath . '/app'));
        $this->container->phpView
            ->configure(array($this->container->phpViewLocator))
            ->queue('setHelperContainer', array($this->container->phpViewHelperContainer));
        $this->container->phpViewHelperContainer
            ->queue('setFormatter', array(function($name) {
                $name = ucfirst($name);
                return "\\Helper\\{$name}";
            }));
        $this->container->phpViewHelperContainer
            ->queue('configure', array('css', array('css')))
            ->queue('configure', array('js', array('js')))
            ->queue('configure', array('lang', array(
                $this->container->langLocator,
                $this->container->phpView,
                'en-us'
            )));
        $this->container->phpViewLocator
            ->queue('addPath', array($this->basePath . '/app/View'));
        $this->container->router
            ->queue('setRoute', array($this->container->defaultRoute));
    }
    
    /**
     * Bootstraps the plugins.
     * 
     * @return void
     */
    public function bootstrapPlugins()
    {
        foreach (Directory::open($this->pluginPath) as $item) {
            $moduleBasePath = $item->getPathname() . DIRECTORY_SEPARATOR;
            $this->container->langLocator->queue('addPath', array($moduleBasePath . 'app/Lang'));
            $this->container->loaderLocator->queue('addPath', array($moduleBasePath . 'app'));
            $this->container->loaderLocator->queue('addPath', array($moduleBasePath . 'lib'));
            $this->container->phpViewLocator->queue('addPath', array($moduleBasePath . 'app/View'));
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