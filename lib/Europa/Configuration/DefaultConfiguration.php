<?php

namespace Europa\Configuration;
use Europa\Di\ConfigurationAbstract;

/**
 * The default configuration.
 */
class DefaultConfiguration extends ConfigurationAbstract
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
     * @return \Europa\Di\Configuration\Default
     */
    public function __construct(array $conf = array())
    {
        $this->conf = array_merge($this->conf, $conf);
        $this->path = realpath(dirname(__FILE__) . '/../../../');
    }
    
    /**
     * Configures the dependency injection container.
     * 
     * @return void
     */
    public function map($container)
    {
        $container->map(array(
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
    public function controllerContainer($container)
    {
        $container->controllerContainer->setFilter($container->controllerContainerFilter);
    }
    
    /**
     * Configures the controller name filter.
     * 
     * @return void
     */
    public function controllerContainerFilter($container)
    {
        $container->controllerContainerFilter(array('prefix' => 'Controller\\'));
    }
    
    /**
     * Configures the dispatcher to use the controller container.
     * 
     * @return void
     */
    public function dispatcher($container)
    {
        $container->dispatcher($container->controllerContainer);
    }
    
    /**
     * Configures the locator instance for the language helper.
     * 
     * @return void
     */
    public function lang($container)
    {
        $container->langLocator->addPath($this->path . '/app/Lang', 'ini');
    }
    
    /**
     * Configures the class loader and the locator for the class files.
     * 
     * @return void
     */
    public function loader($container)
    {
        $container->loader->setLocator($container->loaderLocator);
        $container->loaderLocator->addPath($this->path . '/app');
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @return void
     */
    public function view($container)
    {
        $container->view($container->viewLocator);
        $container->view->setHelperContainer($container->viewHelperContainer);
    }
    
    /**
     * Configures the DI container attached to the view that manages helpers.
     * 
     * @return void
     */
    public function viewHelperContainer($container)
    {
        $container->viewHelperContainer
            ->setFilter($container->viewHelperContainerFilter)
            ->css('css')
            ->html($container->view)
            ->js('js')
            ->lang($container->langLocator, $container->view, 'en-us');
    }
    
    /**
     * Configures the filter that formats helper names for the view.
     * 
     * @return void
     */
    public function viewHelperContainerFilter($container)
    {
        $container->viewHelperContainerFilter(array('prefix' => 'Helper\\'));
    }
    
    /**
     * Configures the locator that locates view files.
     * 
     * @return void
     */
    public function viewLocator($container)
    {
        $container->viewLocator->addPath($this->path . '/app/View');
    }
    
    /**
     * Registers autoloading. Done after all configuration is set.
     * 
     * @return void
     */
    public function registerAutoloading($container)
    {
        $container->loader->register();
    }
}
