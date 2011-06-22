<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../lib/Europa/Bootstrapper.php';

use Europa\Bootstrapper as ParentBootstrapper;
use Europa\Fs\Directory;
use Europa\Fs\Loader;
use Europa\Route\Regex;
use Europa\ServiceLocator;
use Europa\String;

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
     * The application base path.
     * 
     * @var string
     */
    private $base;
    
    /**
     * The main class loader.
     * 
     * @var \Europa\Loader\ClassLoader
     */
    private $loader;
    
    /**
     * The main service locator instance.
     * 
     * @var \Europa\ServiceLocator
     */
    private $locator;
    
    /**
     * Sets default options.
     * 
     * @return Bootstrapper
     */
    public function __construct()
    {
        $this->setOptions(array(
            'pluginPath' => dirname(__FILE__) . '/../plugins'
        ));
        $this->base = realpath(dirname(__FILE__) . '/../');
    }
    
    /**
     * Adds load paths to the loader for autoloading.
     * 
     * @return void
     */
    public function setUpLoaders()
    {
        require $this->base . '/lib/Europa/Fs/Loader.php';
        $this->loader = new Loader;
        $this->loader->register();
    }
    
    /**
     * Sets up the default service locator instance and applies class mapping for the services that will be used in the
     * application.
     * 
     * @return void
     */
    public function configureServiceLocator()
    {
        $this->locator = ServiceLocator::getInstance();
        $this->locator->map('phpView', '\Europa\View\Php');
        $this->locator->map('phpViewHelper', '\Europa\ServiceLocator');
        $this->locator->map('phpViewLocator', '\Europa\Fs\Locator');
        $this->locator->map('request', '\Europa\Request\Http');
        $this->locator->map('router', '\Europa\Router');
        $this->locator->map('loaderLocator', '\Europa\Fs\Locator');
    }
    
    /**
     * Sets the loader locator.
     * 
     * @return void
     */
    public function setUpLoaderLocator()
    {
        $this->locator->loaderLocator->addPath($this->base . '/app');
        $this->loader->setLocator($this->locator->loaderLocator);
    }
    
    /**
     * Configures the router using the service locator.
     * 
     * @return void
     */
    public function configureRouter()
    {
        $this->locator->queueMethodFor('router', 'setRoute', array(
            'default',
            new Regex('(index\.php)?/?(?<controller>.+)?', null, array('controller' => 'index'))
        ));
    }
    
    /**
     * Configures how view helpers behave using the service locator. View helper behavior derives from a configured
     * \Europa\ServiceLocator that is injected into a view instance.
     * 
     * @return void
     */
    public function configureViewHelpers()
    {
        $this->locator->queueMethodFor('phpViewHelper', 'setFormatter', array(function($service) {
            return '\Helper' . String::create($service)->toClass();
        }));
    }
    
    /**
     * Configures the view locator.
     * 
     * @return void
     */
    public function configureViewLocator()
    {
        $this->locator->queueMethodFor('phpViewLocator', 'addPath', array($this->base . '/app/View'));
    }
    
    /**
     * Configures the main view instance. Uses the pre-configured helper and view loader.
     * 
     * @return void
     */
    public function configureView()
    {
        $this->locator->setConfigFor('phpView', array($this->locator->phpViewLocator));
        $this->locator->queueMethodFor('phpView', 'setHelperLocator', array($this->locator->phpViewHelper));
    }
    
    /**
     * Bootstraps the plugins.
     * 
     * @return void
     */
    public function bootstrapPlugins()
    {
        foreach (Directory::open($this->pluginPath) as $item) {
            $base = $item->getPathname() . DIRECTORY_SEPARATOR;
            $this->locator->loaderLocator->addPath($base . 'app');
            $this->locator->loaderLocator->addPath($base . 'lib');
            $this->locator->phpViewLocator->addPath($base . '/app/View');
        }
    }
}