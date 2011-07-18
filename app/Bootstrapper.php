<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../lib/Europa/Bootstrapper/BootstrapperAbstract.php';

use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\ClassLoader;
use Europa\Di\Container;
use Europa\Fs\Directory;
use Europa\Response\Response;
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
            'controllerContainer' => '\Europa\Di\Container',
            'defaultRoute'        => '\Europa\Router\Route\Regex',
            'dispatcher'          => '\Europa\Dispatcher\Dispatcher',
            'finder'              => '\Europa\Fs\Finder',
            'langLocator'         => '\Europa\Fs\Locator',
            'loader'              => '\Europa\ClassLoader',
            'loaderLocator'       => '\Europa\Fs\Locator',
            'view'                => '\Europa\View\Php',
            'viewHelperContainer' => '\Europa\Di\Container',
            'viewLocator'         => '\Europa\Fs\Locator',
            'request'             => '\Europa\Request\Http',
            'response'            => '\Europa\Response\Response',
            'router'              => '\Europa\Router\Basic'
        ));
    }
    
    /**
     * Configures the dispatcher.
     * 
     * @return void
     */
    public function configureDispatcher()
    {
        $this->container->dispatcher(
            $this->container->controllerContainer,
            $this->container->viewContainer
        );
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
        $this->container->router->setRoute('default', $this->container->defaultRoute);
        $this->container->defaultRoute(
            '(index\.php)?/?(?<controller>.+)?',
            null,
            array('controller' => 'index')
        );
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
     * Configures the sample plugin system.
     * 
     * @return void
     */
    public function bootstrapPlubins()
    {
        $finder = $this->container->finder->create();
        $finder->in($this->pluginPath);
        $finder->depth(0);
        foreach ($finder as $item) {
            $moduleBasePath = $item->getPathname() . DIRECTORY_SEPARATOR;
            $this->container->langLocator->addPath($moduleBasePath . 'app/Lang', 'ini');
            $this->container->loaderLocator->addPath($moduleBasePath . 'app');
            $this->container->loaderLocator->addPath($moduleBasePath . 'lib');
            $this->container->viewLocator->addPath($moduleBasePath . 'app/View');
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
