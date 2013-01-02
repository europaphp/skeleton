<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\ServiceContainer;
use Europa\Event\Manager as EventManager;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Module\Manager as ModuleManager;
use Europa\Request\RequestAbstract;
use Europa\Response\ResponseAbstract;
use Europa\Router\Router;
use Europa\View\HelperConfiguration;
use Europa\View\Negotiator;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;

class AppConfiguration extends ConfigurationAbstract implements AppConfigurationInterface
{
    public function config($defaults, $config)
    {
        return new Config($defaults, $config);
    }

    public function event()
    {
        return new EventManager;
    }

    public function loader()
    {
        $loader = new Loader;
        $loader->setLocator($this->loaderLocator);
        return $loader;
    }

    public function loaderLocator()
    {
        return new Locator;
    }

    public function modules()
    {
        return new ModuleManager($this);
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router()
    {
        return new Router;
    }

    public function view()
    {
        $view = $this->viewNegotiator($this->request);

        if ($view instanceof ViewScriptInterface) {
            $view->setScriptLocator($this->viewLocator);
            $view->setScript($this->viewScript);
            $view->setScriptSuffix($this->config['viewSuffix']);
        }

        if ($view instanceof Php) {
            $view->setServiceContainer($this->viewHelpers);
        }

        return $view;
    }

    public function viewHelperConfiguration()
    {
        $helperConfiguration = new HelperConfiguration;
        $helperConfiguration->setArguments('uri', $this->router);
        return $helperConfiguration;
    }

    public function viewHelpers()
    {
        $helpers = new ServiceContainer;
        $helpers->configure($this->viewHelperConfiguration);
        return $helpers;
    }

    public function viewLocator()
    {
        return new Locator;
    }

    public function viewNegotiator()
    {
        return new Negotiator;
    }

    /**
     * @transient
     */
    public function viewScript()
    {
        $format = $this->config['viewScriptFormat'];

        if (is_callable($format)) {
            return $format($this->request);
        }

        foreach ($this->request->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }
}