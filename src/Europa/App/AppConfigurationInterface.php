<?php

namespace Europa\App;
use Europa\Config\ConfigInterface;
use Europa\Module\ManagerInterface;
use Europa\Request\RequestInterface;
use Europa\Router\Router;

interface AppConfigurationInterface
{
    public function config();

    public function event(ConfigInterface $config);

    public function loader(callable $loaderLocator);

    public function loaderLocator(ConfigInterface $config, ManagerInterface $modules);

    public function modules(ConfigInterface $config);

    public function request();

    public function response();

    public function router(ManagerInterface $modules);

    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        callable $viewLocator,
        callable $viewHelpers,
        callable $viewNegotiator,
        callable $viewScriptFormatter
    );

    public function viewHelpers(Router $router);

    public function viewLocator(ConfigInterface $config, ManagerInterface $modules);

    public function viewNegotiator(ConfigInterface $config);

    public function viewScriptFormatter();
}