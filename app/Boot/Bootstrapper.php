<?php

namespace Boot;

require_once dirname(__FILE__) . '/../../lib/Europa/Bootstrapper/BootstrapperInterface.php';
require_once dirname(__FILE__) . '/../../lib/Europa/Bootstrapper/BootstrapperAbstract.php';

use Europa\Bootstrapper\BootstrapperAbstract;
use Europa\Fs\Loader;
use Europa\Di\Container;

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
        $this->container = Container::get()->map(array(
            'controllerContainer'       => '\Europa\Di\Container',
            'controllerContainerFilter' => '\Europa\Filter\ClassNameFilter',
            'dispatcher'                => '\Europa\Dispatcher\Dispatcher',
            'langLocator'               => '\Europa\Fs\Locator\PathLocator',
            'loader'                    => '\Europa\Fs\Loader',
            'loaderLocator'             => '\Europa\Fs\Locator\PathLocator',
            'request'                   => '\Europa\Request\Http',
            'response'                  => '\Europa\Response\Http',
            'view'                      => '\Europa\View\Php',
            'viewHelperContainer'       => '\Europa\Di\Container',
            'viewHelperContainerFilter' => '\Europa\Filter\ClassNameFilter',
            'viewLocator'               => '\Europa\Fs\Locator\PathLocator'
        ));
    }
    
    /**
     * Configures the controller container.
     * 
     * @return void
     */
    public function configureControllerContainer()
    {
        $this->container->controllerContainer->setFilter($this->container->controllerContainerFilter);
    }
    
    /**
     * Configures the controller name filter.
     * 
     * @return void
     */
    public function configureControllerContainerFilter()
    {
        $this->container->controllerContainerFilter(array('prefix' => 'Controller\\'));
    }
    
    /**
     * Configures the dispatcher to use the controller container.
     * 
     * @return void
     */
    public function configureDispatcher()
    {
        $this->container->dispatcher($this->container->controllerContainer);
    }
    
    /**
     * Configures the locator instance for the language helper.
     * 
     * @return void
     */
    public function configureLang()
    {
        $this->container->langLocator->addPath($this->basePath . '/app/Lang', 'ini');
    }
    
    /**
     * Configures the class loader and the locator for the class files.
     * 
     * @return void
     */
    public function configureLoader()
    {
        $this->container->loader->setLocator($this->container->loaderLocator);
        $this->container->loaderLocator->addPath($this->basePath . '/app');
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
            ->setFilter($this->container->viewHelperContainerFilter)
            ->css('css')
            ->html($this->container->view)
            ->js('js')
            ->lang($this->container->langLocator, $this->container->view, 'en-us');
    }
    
    /**
     * Configures the filter that formats helper names for the view.
     * 
     * @return void
     */
    public function configureViewHelperContainerFilter()
    {
        $this->container->viewHelperContainerFilter(array('prefix' => 'Helper\\'));
    }
    
    /**
     * Configures the locator that locates view files.
     * 
     * @return void
     */
    public function configureViewLocator()
    {
        $this->container->viewLocator->addPath($this->basePath . '/app/View');
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
