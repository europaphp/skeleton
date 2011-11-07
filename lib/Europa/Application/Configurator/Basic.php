<?php

namespace Europa\Application\Configurator;
use Europa\Application\ConfiguratorAbstract;
use Europa\Application\Container;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\PathLocator;

/**
 * The default configuration.
 * 
 * @category Configurators
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Basic extends ConfiguratorAbstract
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
    private $conf = array();
    
    /**
     * Sets default options.
     * 
     * @param array $conf Configuration to granularize the default configuration.
     * 
     * @return \Europa\Application\Configurator\Basic
     */
    public function __construct(array $conf = array())
    {
        $this->conf = array_merge($this->conf, $conf);
        $this->path = realpath(dirname(__FILE__) . '/../../../../');
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @return void
     */
    public function map($container)
    {
        $container->map(array(
            'dispatcher' => '\Europa\Dispatcher\Dispatcher',
            'loader'     => '\Europa\Fs\Loader',
            'request'    => '\Europa\Request\Http',
            'response'   => '\Europa\Response\Http',
            'view'       => '\Europa\View\Php',
        ));
    }
    
    /**
     * Configures the dispatcher to use the controller container.
     * 
     * @return void
     */
    public function dispatcher($container)
    {
        $controllers = new Container;
        $controllers->setFilter(new ClassNameFilter(array('prefix' => 'Controller\\')));
        $container->resolve('dispatcher')->configure(array($controllers));
    }
    
    /**
     * Configures the class loader and the locator for the class files.
     * 
     * @return void
     */
    public function loader($container)
    {
        $locator = new PathLocator;
        $locator->addPath($this->path . '/app');
        $container->resolve('loader')->queue('setLocator', array($locator));
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @return void
     */
    public function view($container)
    {
        $locator = new PathLocator;
        $locator->addPath($this->path . '/app/View');
        $container->resolve('view')->configure(array($locator));
    }
    
    /**
     * Configures helpers.
     * 
     * @return void
     */
    public function helpers($container)
    {
        $locator = new PathLocator;
        $locator->throwWhenAdding(false)->addPath($this->path . '/app/Lang', 'ini');
        
        $helpers = new Container;
        $helpers->setFilter(new ClassNameFilter(array('prefix' => '\Europa\View\Helper\\')));
        $helpers->resolve('css')->configure(array('css'));
        $helpers->resolve('html')->configure(array($container->resolve('view')));
        $helpers->resolve('js')->configure(array('js'));
        $helpers->resolve('lang')->configure(array($locator, $container->resolve('view'), 'en-us'));
        
        $container->resolve('view')->queue('setHelperContainer', array($helpers));
    }
}
