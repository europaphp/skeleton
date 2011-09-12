<?php

namespace Boot;

require_once dirname(__FILE__) . '/../../lib/Europa/Bootstrapper/BootstrapperInterface.php';
require_once dirname(__FILE__) . '/../../lib/Europa/Bootstrapper/BootstrapperAbstract.php';

use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\ClassLoader;
use Europa\Di\Container;
use Europa\Fs\Directory;
use Europa\StringObject;

/**
 * Bootstraps the sample application.
 * 
 * @category Bootstrapping
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Bootstrapper extends BootstrapperAbstract
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
     * @return \Bootstrapper
     */
    public function __construct()
    {
        $this->setOptions(array(
            'basePath' => realpath(dirname(__FILE__) . '/../../')
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
            'dispatcher'          => '\Europa\Dispatcher\Dispatcher',
            'finder'              => '\Europa\Fs\Finder',
            'langLocator'         => '\Europa\Fs\Locator\PathLocator',
            'loader'              => '\Europa\ClassLoader',
            'loaderLocator'       => '\Europa\Fs\Locator\PathLocator',
            'view'                => '\Europa\View\Php',
            'viewHelperContainer' => '\Europa\Di\Container',
            'viewLocator'         => '\Europa\Fs\Locator\PathLocator',
            'request'             => '\Europa\Request\Http',
            'response'            => '\Europa\Response\Response',
            'route'               => '\Europa\Router\Route\RegexRoute',
            'router'              => '\Europa\Router\RequestRouter',
            'routeResolver'       => '\Europa\Router\Resolver\RouteResolver'
        ));
    }
    
    /**
     * Configures the locator instance for the language helper.
     * 
     * @return void
     */
    public function configureLang()
    {
        $this->container->langLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app/Lang', 'ini');
    }
    
    /**
     * Configures the class loader and the locator for the class files.
     * 
     * @return void
     */
    public function configureLoader()
    {
        $this->container->loader->setLocator($this->container->loaderLocator);
        $this->container->loaderLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app');
    }
    
    /**
     * Configures the router with routes.
     * 
     * @return void
     */
    public function configureRouter()
    {
        $this->container->router($this->container->routeResolver);
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @return void
     */
    public function configureView()
    {
        $this->container->view($this->container->viewLocator);
        $this->container->view->setHelperContainer($this->container->viewHelperContainer);
    }
    
    /**
     * Configures the DI container attached to the view that manages helpers.
     * 
     * @return void
     */
    public function configureViewHelperContainer()
    {
        $this->container->viewHelperContainer
            ->setFormatter(function($name) {
                $name = ucfirst($name);
                return "\\Helper\\{$name}";
            })
            ->css('css')
            ->html($this->container->view)
            ->js('js')
            ->lang($this->container->langLocator, $this->container->view, 'en-us');
    }
    
    /**
     * Configures the locator that locates view files.
     * 
     * @return void
     */
    public function configureViewLocator()
    {
        $this->container->viewLocator
            ->throwWhenAdding(false)
            ->addPath($this->basePath . '/app/View');
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
