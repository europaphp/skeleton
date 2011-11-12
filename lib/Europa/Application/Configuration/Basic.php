<?php

namespace Europa\Application\Configuration;
use Europa\Application\ConfigurationInterface;
use Europa\Application\Container;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\MapFilter;
use Europa\Fs\Locator\PathLocator;
use Europa\Request\RequestAbstract;

/**
 * The default configuration.
 * 
 * @category Configurations
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Basic implements ConfigurationInterface
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
        'addIncludePaths'  => false,
    	'controllerPrefix' => 'Controller\\',
    	'controllerSuffix' => '',
    	'helperPrefix'     => 'Europa\View\Helper\\',
    	'helperSuffix'     => '',
    	'langPaths'        => array('app/Lang/en-us' => 'ini'),
    	'loadPaths'        => array('app' => 'php'),
    	'path'             => '..',
    	'viewPaths'        => array('app/View' => 'php')
    );
    
    /**
     * Sets default options.
     * 
     * @param array $conf Configuration to granularize the default configuration.
     * 
     * @return \Europa\Application\Configuration\Basic
     */
    public function __construct(array $conf = array())
    {
        $this->conf = array_merge($this->conf, $conf);
        $this->conf['path'] = realpath($this->conf['path']);
    }
    
    /**
     * Configures the specified container.
     * 
     * @param \Europa\Application\Container $container The container to configure.
     * 
     * @return void
     */
    public function configure(Container $container)
    {
    	$this->map($container);
    	$this->dispatcher($container);
    	$this->loader($container);
    	$this->view($container);
    	$this->helpers($container);
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @return void
     */
    private function map($container)
    {
        $interface = RequestAbstract::isCli() ? 'Cli' : 'Http';
        $container->setFilter(new MapFilter(array(
            'dispatcher' => '\Europa\Dispatcher\Dispatcher',
            'loader'     => '\Europa\Fs\Loader',
            'request'    => '\Europa\Request\\' . $interface,
            'response'   => '\Europa\Response\\' . $interface,
            'router'     => '\Europa\Router\Router',
            'view'       => '\Europa\View\Php',
        )));
    }
    
    /**
     * Configures the dispatcher to use the controller container.
     * 
     * @return void
     */
    private function dispatcher($container)
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
     * @return void
     */
    private function loader($container)
    {
        $locator = new PathLocator;
        foreach ($this->conf['loadPaths'] as $path => $suffix) {
            $path = $this->conf['path'] . '/' . trim($path, '/\\');
            $locator->addPath($path, $suffix);
            if ($this->conf['addIncludePaths']) {
                $locator->addIncludePath($path);
            }
        }
        $container->resolve('loader')->queue('setLocator', array($locator));
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @return void
     */
    private function view($container)
    {
        $locator = new PathLocator;
        $locator->throwWhenLocating(true);
        foreach ($this->conf['viewPaths'] as $path => $suffix) {
            $locator->addPath($this->conf['path'] . '/' . trim($path, '/\\'), $suffix);
        }
        $container->resolve('view')->configure(array($locator));
    }
    
    /**
     * Configures helpers.
     * 
     * @return void
     */
    private function helpers($container)
    {
        $locator = new PathLocator;
        $locator->throwWhenAdding(false);
        
        foreach ($this->conf['langPaths'] as $path => $suffix) {
	        $locator->addPath($this->conf['path'] . '/' . trim($path, '/\\'), $suffix);
	    }
        
        $helpers = new Container;
        $helpers->setFilter(new ClassNameFilter(array(
        	'prefix' => $this->conf['helperPrefix'],
        	'suffix' => $this->conf['helperSuffix']
        )));
        $helpers->resolve('lang')->configure(array($container->resolve('view'), $locator));
        $helpers->resolve('uri')->configure(array($container->resolve('router')));
        
        $container->resolve('view')->queue('setHelperContainer', array($helpers));
    }
}
