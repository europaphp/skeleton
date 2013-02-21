<?php

namespace Europa\App;
use Closure;
use Europa\Config\ConfigInterface;
use Europa\Di\DependencyInjectorInterface;
use Europa\Fs\LocatorInterface;
use Europa\Module\ManagerInterface;
use Europa\Request\RequestInterface;
use Europa\Router\RouterInterface;
use Europa\View\NegotiatorInterface;

interface AppConfigurationInterface
{
    public function config();

    public function controllers();

    public function events();

    public function loader(LocatorInterface $loaderLocators);

    public function loaderLocators();

    public function modules();

    public function request();

    public function response();

    public function routers();

    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        LocatorInterface $viewLocators,
        DependencyInjectorInterface $viewHelpers,
        NegotiatorInterface $viewNegotiator,
        Closure $viewScriptFormatter
    );

    public function viewHelpers();

    public function viewLocators(ConfigInterface $config);

    public function viewNegotiator(ConfigInterface $viewNegotiatorConfig, RequestInterface $request);

    public function viewScriptFormatter(ConfigInterface $config);
}