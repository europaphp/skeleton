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
use Traversable;

interface AppConfigurationInterface
{
    /**
     * @return Europa\Config\ConfigInterface
     */
    public function config();

    /**
     * @return Europa\Di\FinderInterface
     */
    public function controllers(DependencyInjectorInterface $self);

    /**
     * @return Europa\Event\ManagerInterface
     */
    public function events();

    /**
     * @return Europa\Fs\LoaderInterface
     */
    public function loader(LocatorInterface $loaderLocators);

    /**
     * @return Traversable
     */
    public function loaderLocators();

    /**
     * @return Europa\Module\ManagerInterface
     */
    public function modules();

    /**
     * @return Europa\Request\RequestInterface
     */
    public function request();

    /**
     * @return Europa\Response\ResponseInterface
     */
    public function response();

    /**
     * @return Europa\Router\RouterInterface
     */
    public function router(Traversable $routers);

    /**
     * @return Traversable
     */
    public function routers();

    /**
     * @return Europa\View\ViewInterface
     */
    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        LocatorInterface $viewLocators,
        DependencyInjectorInterface $viewHelpers,
        NegotiatorInterface $viewNegotiator,
        Closure $viewScriptFormatter
    );

    /**
     * @return Europa\Di\DependencyInjectorInterface
     */
    public function viewHelperInjector(Traversable $viewHelperInjectors);

    /**
     * @return Traversable
     */
    public function viewHelperInjectors();

    /**
     * @return Europa\Fs\LocatorInterface
     */
    public function viewLocator(Traversable $viewLocators);

    /**
     * @return Traversable
     */
    public function viewLocators();

    /**
     * @return Europa\View\NegotiatorInterface
     */
    public function viewNegotiator(ConfigInterface $viewNegotiatorConfig, RequestInterface $request);

    /**
     * @return Closure
     */
    public function viewScriptFormatter(ConfigInterface $config);
}