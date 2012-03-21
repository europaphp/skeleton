<?php

namespace Europa\Di\Configuration;
use Europa\Di\Container;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\MapFilter;
use Europa\Fs\Locator\Locator;
use Europa\Request\RequestAbstract;

/**
 * The default configuration.
 * 
 * @category Configurations
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Standard extends ConfigurationAbstract
{
    /**
     * The application base path.
     * 
     * @var string
     */
    private $path;
    
    /**
     * Smaller configuration options that slightly affect the behavior of the default configuration.
     * 
     * @var array
     */
    private $conf = array(
        'addIncludePaths'  => true,
        'controllerPrefix' => 'Controller\\',
        'controllerSuffix' => '',
        'langPaths'        => array('app/langs/en-us' => 'ini'),
        'loadPaths'        => array('app/classes'),
        'viewPaths'        => array('app/views')
    );
    
    /**
     * Sets default options.
     * 
     * @param array $conf Configuration to granularize the default configuration.
     * 
     * @return Standard
     */
    public function __construct(array $conf = array())
    {
        // set default path
        $this->conf['path'] = dirname(__FILE__) . '/../../../../';

        // apply configuration
        $this->conf = array_merge($this->conf, $conf);

        // get the translated path
        $this->conf['path'] = realpath($this->conf['path']);
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function map(Container $container)
    {
        $container->addFilter(new MapFilter(array(
            'controllers' => '\Europa\Di\Container',
            'dispatcher'  => '\Europa\Dispatcher\Dispatcher',
            'helpers'     => '\Europa\Di\Container',
            'loader'      => '\Europa\Fs\Loader',
            'router'      => '\Europa\Router\Router',
            'view'        => '\Europa\View\Php',
        )));
    }
    
    /**
     * Configures the dispatcher to use the controller container.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function dispatcher(Container $container)
    {
        $dispatcher = $container->resolve('dispatcher');
        $dispatcher->queue('setRouter', array($container->resolve('router')));
        $dispatcher->queue('setControllerFilter', array(new ClassNameFilter(array(
            'prefix' => $this->conf['controllerPrefix'],
            'suffix' => $this->conf['controllerSuffix']
        ))));
    }
    
    /**
     * Configures the class loader and the locator for the class files.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function loader(Container $container)
    {
        $locator = new Locator($this->conf['path']);
        $locator->addPaths($this->conf['loadPaths']);
        $container->resolve('loader')->queue('setLocator', array($locator));

        if ($this->conf['addIncludePaths']) {
            $locator->addIncludePaths($this->conf['loadPaths']);
        }
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function view(Container $container)
    {
        $locator = new Locator($this->conf['path']);
        $locator->throwWhenLocating(true);
        $locator->addPaths($this->conf['viewPaths']);
        $container->resolve('view')->configure(array($locator));
    }
    
    /**
     * Configures helpers.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function helpers(Container $container)
    {
        // the locator for the lang helper
        $locator = new Locator($this->conf['path']);
        $locator->throwWhenAdding(false);
        $locator->addPaths($this->conf['langPaths']);
        
        // the default helper setup
        $helpers = $container->getService('helpers');
        $helpers->addFilter(new ClassNameFilter(array('prefix' => '\Europa\View\Helper\\')));
        $helpers->addFilter(new ClassNameFilter(array('prefix' => '\Helper\\')));
        
        // default helper configuration
        $helpers->resolve('lang')->configure(array($container->resolve('view'), $locator));
        $helpers->resolve('uri')->configure(array($container->resolve('router')));
        
        // add the helper container array to the view
        $container->resolve('view')->queue('setHelperContainer', array($helpers));
    }
}