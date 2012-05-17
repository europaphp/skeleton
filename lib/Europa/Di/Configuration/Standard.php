<?php

namespace Europa\Di\Configuration;
use Europa\Di\Container;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\ClassResolutionFilter;
use Europa\Filter\MapFilter;
use Europa\Fs\Loader;
use Europa\Fs\Locator\Locator;
use Europa\Request\RequestAbstract;
use Europa\View\ViewInterface;
use Europa\View\ViewScriptInterface;

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
    private $conf = [
        'addIncludePaths'  => true,
        'classPaths'       => ['app/classes'],
        'controllerPrefix' => 'Controller\\',
        'controllerSuffix' => '',
        'langPaths'        => ['app/langs/en-us' => 'ini'],
        'viewPaths'        => ['app/views']
    ];
    
    /**
     * Sets default options.
     * 
     * @param array $conf Configuration to granularize the default configuration.
     * 
     * @return Standard
     */
    public function __construct(array $conf = [])
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
        $cli = RequestAbstract::isCli();
        $container->setFilter(new MapFilter([
            'app'         => 'Europa\App\App',
            'controllers' => 'Europa\Di\Container',
            'event'       => 'Europa\Event\Dispatcher',
            'helpers'     => 'Europa\Di\Container',
            'loader'      => 'Europa\Fs\Loader',
            'request'     => $cli ? 'Europa\Request\Cli' : 'Europa\Request\Http',
            'response'    => $cli ? 'Europa\Response\Cli' : 'Europa\Response\Http',
            'router'      => 'Europa\Router\Router',
            'view'        => 'Europa\View\Php'
        ]));
    }
    
    /**
     * Configures the app.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function app(Container $container)
    {
        $container->resolve('app')->config(function() use ($container) {
            return $container;
        });
    }
    
    /**
     * Sets up the controllers.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function controllers(Container $container)
    {
        $container->resolve('controllers')->queue(function(Container $controllers) use ($container) {
            $controllers->config('Europa\Controller\ControllerInterface', function() use ($container) {
                return [$container->request, $container->response];
            });
            
            $controllers->setFilter(new ClassNameFilter([
                'prefix' => $this->conf['controllerPrefix'],
                'suffix' => $this->conf['controllerSuffix']
            ]));
        });
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
        $container->resolve('loader')->queue(function(Loader $loader) {
            $locator = new Locator($this->conf['path']);
            $locator->addPaths($this->conf['classPaths']);
            
            if ($this->conf['addIncludePaths']) {
                $locator->addIncludePaths($this->conf['classPaths']);
            }
            
            $loader->setLocator($locator);
        });
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
        $view = $container->resolve('view');
        
        $view->config(function() {
            $locator = new Locator($this->conf['path']);
            $locator->throwWhenLocating(true);
            $locator->addPaths($this->conf['viewPaths']);
            
            return $locator;
        });
        
        $view->queue(function(ViewScriptInterface $view) use ($container) {
            $view->setHelperContainer($container->helpers);
        });
        
        $view->queue(function(ViewScriptInterface $view) use ($container) {
            $interface  = RequestAbstract::isCli() ? 'cli' : 'web';
            $controller = str_replace(' ', '/', $container->app->getController());
            
            $view->setScript($interface . '/' . $controller);
        });
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
        $container->resolve('helpers')->queueAll([
            function(Container $helpers) {
                $helpers->setFilter(new ClassResolutionFilter([
                    new ClassNameFilter(['prefix' => '\Europa\View\Helper\\'])
                ]));
            },
            
            function(Container $helpers) use ($container) {
                $locator = new Locator($this->conf['path']);
                $locator->throwWhenAdding(false);
                $locator->addPaths($this->conf['langPaths']);
                
                $helpers->resolve('lang')->config(function() use ($container, $locator) {
                    return [$container->view, $locator];
                });
            },
            
            function(Container $helpers) use ($container) {
                $helpers->resolve('uri')->config(function() use ($container) {
                    return $container->router;
                });
            }
        ]);
    }
}