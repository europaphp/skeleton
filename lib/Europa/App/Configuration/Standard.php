<?php

namespace Europa\App\Configuration;
use Europa\Di\Container;
use Europa\Di\ConfigurationAbstract;
use Europa\Filter\ClassNameFilter;
use Europa\Filter\ClassResolutionFilter;
use Europa\Filter\MapFilter;
use Europa\Filter\MapRegexFilter;
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
        'addIncludePaths'    => true,
        'classPaths'         => ['app/classes'],
        'controllerPrefix'   => 'Controller\\',
        'controllerSuffix'   => '',
        'langPaths'          => ['app/langs/en-us' => 'ini'],
        'viewContentTypeMap' => [
            '/json/i' => 'application/json',
            '/xml/i'  => 'application/xml',
            '/.*/'    => 'text/html'
        ],
        'viewPaths'          => ['app/views'],
        'viewMap'            => [
            '/(application\/)?json/' => 'Europa\View\Json',
            '/(application\/)?xml/'  => 'Europa\View\Xml',
            '/.+/'                   => 'Europa\View\Php'
        ]
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
            'loader'      => 'Europa\Fs\Loader',
            'request'     => $cli ? 'Europa\Request\Cli' : 'Europa\Request\Http',
            'response'    => $cli ? 'Europa\Response\Cli' : 'Europa\Response\Http',
            'router'      => 'Europa\Router\Router',
            'views'       => 'Europa\Di\Container'
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
        $container->resolve('app')->config($container);
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
        $container->resolve('controllers')->queue(function($controllers) use ($container) {
            $controllers->config('Europa\Controller\ControllerInterface', function() use ($container) {
                return [$container->request, $container->response];
            });
            
            $controllers->setFilter(new ClassNameFilter([
                'prefix' => $this->conf['controllerPrefix'],
                'suffix' => $this->conf['controllerSuffix']
            ]));
            
            $controllers->queue('Europa\Di\Pluggable', function($controller) use ($container) {
                $plugins = $controller->getPluginContainer();
                $plugins->setFilter(new ClassNameFilter([
                    'prefix' => 'Europa\Controller\Plugin\\'
                ]));
            });
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
        $container->resolve('loader')->queue(function($loader) {
            $locator = new Locator($this->conf['path']);
            $locator->addPaths($this->conf['classPaths']);
            
            if ($this->conf['addIncludePaths']) {
                $locator->addIncludePaths($this->conf['classPaths']);
            }
            
            $loader->setLocator($locator);
        });
    }
    
    /**
     * Configures the response to use a view content type mapping.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function response(Container $container)
    {
        $container->queue('Europa\Response\Http', function($response) {
            $response->setContentTypeFilter(new MapRegexFilter($this->conf['viewContentTypeMap']));
        });
    }
    
    /**
     * Configures the PHP view specifically since it requires a locator and optional helper.
     * 
     * @param Container $container The container to configure.
     * 
     * @return void
     */
    public function views(Container $container)
    {
        $container->resolve('views')->queue(function($views) use ($container) {
            $views->setFilter(new MapRegexFilter($this->conf['viewMap']));
            
            $views->queue('Europa\View\ViewScriptInterface', function($view) use ($container) {
                $interface  = RequestAbstract::isCli() ? 'cli' : 'web';
                $controller = str_replace(' ', '/', $container->app->getController());
                
                $view->setScript($interface . '/' . $controller);
            });
            
            $views->config('Europa\View\Php', function() {
                $locator = new Locator($this->conf['path']);
                
                $locator->throwWhenLocating(true);
                $locator->addPaths($this->conf['viewPaths']);
                
                return [$locator];
            });
            
            $views->queue('Europa\View\Php', function($view) use ($container) {
                $view->getPluginContainer()->setFilter(new ClassNameFilter([
                    'prefix' => 'Europa\View\Plugin\\'
                ]));
                
                $locator = new Locator($this->conf['path']);
                $locator->throwWhenAdding(false);
                $locator->addPaths($this->conf['langPaths']);
                
                $view->getPluginContainer()->resolve('lang')->config([$view, $locator]);
                $view->getPluginContainer()->resolve('uri')->config([$container->router]);
            });
        });
    }
}