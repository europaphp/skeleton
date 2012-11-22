<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\ServiceContainer;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Module\Manager;
use Europa\Request\RequestAbstract;
use Europa\Response\ResponseAbstract;
use Europa\Router\Router;
use Europa\View\HelperConfiguration;
use Europa\View\Negotiator;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;

/**
 * The default Application service configuration.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class AppConfiguration extends ConfigurationAbstract implements AppConfigurationInterface
{
    /**
     * Returns the application configuration object.
     * 
     * @param mixed $defaults The default configuration.
     * @param mixed $config   The configuration to use.
     * 
     * @return Config
     */
    public function config($defaults, $config)
    {
        return new Config($defaults, $config);
    }

    /**
     * Returns the loader responsible for auto-loading class files.
     * 
     * @return Loader
     */
    public function loader()
    {
        $loader = new Loader;
        $loader->setLocator($this->loaderLocator);
        return $loader;
    }

    /**
     * Returns the locator responsible for finding class files.
     * 
     * @return Locator
     */
    public function loaderLocator()
    {
        return new Locator;
    }

    /**
     * Returns the module manager.
     * 
     * @return ModuleManager
     */
    public function modules()
    {
        return new ModuleManager($this);
    }

    /**
     * Returns the request.
     * 
     * @return Europa\Request\RequestInterface
     */
    public function request()
    {
        return RequestAbstract::detect();
    }

    /**
     * Returns the response.
     * 
     * @return Europa\Response\ResponseInterface
     */
    public function response()
    {
        return ResponseAbstract::detect();
    }

    /**
     * Returns the router that routes the request to a controller.
     * 
     * @return Router
     */
    public function router()
    {
        return new Router;
    }

    /**
     * Returns the view responsible for rendering the response.
     * 
     * @return Europa\View\ViewInterface
     */
    public function view()
    {
        $view = $this->viewNegotiator($this->request);

        if ($view instanceof ViewScriptInterface) {
            $view->setScriptLocator($this->viewLocator);
            $view->setScript($this->viewScript);
            $view->setScriptSuffix($this->config->viewSuffix);
        }

        if ($view instanceof Php) {
            $view->setServiceContainer($this->viewHelpers);
        }

        return $view;
    }

    /**
     * Returns the helper configuration.
     * 
     * @return HelperConfiguration
     */
    public function viewHelperConfiguration()
    {
        $helperConfiguration = new HelperConfiguration;
        $helperConfiguration->setArguments('uri', $this->router);
        return $helperConfiguration;
    }

    /**
     * Returns a container responsible for handling view helpers.
     * 
     * @return ServiceContainer
     */
    public function viewHelpers()
    {
        $helpers = new ServiceContainer;
        $helpers->configure($this->viewHelperConfiguration);
        return $helpers;
    }

    /**
     * Returns the locator responsible for finding view scripts.
     * 
     * @return Locator
     */
    public function viewLocator()
    {
        return new Locator;
    }

    /**
     * Returns the view negotiator responsible for determining what type of view to render.
     * 
     * @return Negotiator
     */
    public function viewNegotiator()
    {
        return new Negotiator;
    }

    /**
     * Formats the view script so it can be set on a `ViewScriptInterface` object.
     * 
     * @transient
     * 
     * @return string
     */
    public function viewScript()
    {
        $format = $this->config->viewScriptFormat;

        if (is_callable($format)) {
            return $format($this->request);
        }

        foreach ($this->request->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }
}