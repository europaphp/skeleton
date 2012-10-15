<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Controller\ControllerAbstract;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\Locator as DiLocator;
use Europa\Event\Manager as EventManager;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\Locator as FsLocator;
use Europa\Fs\Loader;
use Europa\Module\Manager as ModuleManager;
use Europa\Request;
use Europa\Request\RequestAbstract;
use Europa\Response;
use Europa\Router\Adapter\Ini as IniRouteAdapter;
use Europa\Router\Route\Regex;
use Europa\Router\Router;
use Europa\View\Json;
use Europa\View\Jsonp;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;
use Europa\View\Xml;

/**
 * The default container.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Configuration extends ConfigurationAbstract
{
    /**
     * The default configuration.
     * 
     * - `cliViewPath` The path that cli view scripts reside under.
     * - `controllerFilterConfig` The controller filter configuration used to resolve controller class names.
     * - `helperFilterConfig` The helper filter configuration used to resolve helper class names.
     * - `jsonpCallbackKey` If a content type of JSON is requested - either by using a `.json` suffix or by using an `application/json` content type request header - and this is set in the request, a `Jsonp` view instance is used rather than `Json` and the value of this request parameter is used as the callback.
     * - `classPaths` Class load paths that will be added to the `loaderLocator`.
     * - `langPaths` Language paths and suffixes to supply to the language file locator.
     * - `viewPaths` View paths and suffixes to supply to the view script locator.
     * - `viewTypes` Mapping of content-type to view class mapping.
     * - `webViewPath` The path that web view script reside under.
     * 
     * @var array
     */
    private $config = [
        'app'           => [],
        'paths.app'     => '={root}/app',
        'paths.root'    => '..',
        'paths.classes' => ['classes'     => 'php'],
        'paths.langs'   => ['langs/en-us' => 'ini'],
        'paths.views'   => ['views'       => 'php']
    ];

    /**
     * Sets up the container.
     * 
     * @param string $root   The application install path.
     * @param array  $config The configuration.
     * 
     * @return Container
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
        
        if (!$this->config['paths.root'] = realpath($this->config['paths.root'])) {
            throw new UnexpectedValueException(sprintf(
                'A valid applicaiton root must be specified in the configuration as "root". The path "%s" is not valid.',
                $this->config['paths.root']
            ));
        }
    }

    /**
     * Returns an application service.
     * 
     * @return App
     */
    public function app()
    {
        return new App($this->config->app);
    }

    /**
     * Returns the helper container.
     * 
     * @return ClosureContainer
     */
    public function helpers($container)
    {
        $locator = new DiLocator;

        $locator->args('Europa\View\Helper\Lang', function() use ($container) {
            return [$container->view, $container->langLocator];
        });

        $locator->call('Europa\View\Helper\Uri', function() use ($container) {
            return [$this->router];
        });

        return $locator;
    }

    /**
     * Returns the language file locator.
     * 
     * @return LocatorInterface
     */
    public function langLocator()
    {
        $locator = new FsLocator;
        $locator->setBasePath($this->config['paths.app']);
        return $locator;
    }

    /**
     * Returns the class loader.
     * 
     * @return Loader
     */
    public function loader($container)
    {
        $loader = new Loader;
        $loader->setLocator($container->loaderLocator);
        return $loader;
    }

    /**
     * Returns the class file locator.
     * 
     * @return LocatorInterface
     */
    public function loaderLocator()
    {
        $locator = new FsLocator;
        $locator->setBasePath($this->config['paths.app']);
        return $locator;
    }
}