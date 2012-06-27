<?php

namespace Container;
use Closure;
use Europa\App\App;
use Europa\App\BootFiles;
use Europa\Controller\ControllerAbstract;
use Europa\Di\Provider;
use Europa\Di\Finder;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator;
use Europa\Request;
use Europa\Request\RequestAbstract;
use Europa\Response;
use Europa\Router\RegexRoute;
use Europa\Router\Router;
use Europa\View\Json;
use Europa\View\Php;
use Europa\View\Xml;

/**
 * API repository.
 * 
 * @category Repositories
 * @package  HostingControl
 * @author   Trey Shugart <tshugart@ultraserve.com.au>
 * @license  Copyright (c) Ultra Serve http://ultraserve.com.au/license
 */
class Europa extends Provider
{
    /**
     * Returns an application service.
     * 
     * @return App
     */
    public function app()
    {
        return new App($this);
    }
    
    /**
     * Returns the container responsible for locating controllers.
     * 
     * @param Closure $request Request getter.
     * 
     * @return ClosureContainer
     */
    public function controllers(Closure $request)
    {
        $finder = new Finder;
        $finder->addFilter(new ClassNameFilter(['prefix' => 'Controller\\']));
        $finder->config('Europa\Controller\ControllerInterface', function() use ($request) {
            return [$request()];
        });
        return $finder;
    }
    
    /**
     * Returns the helper container.
     * 
     * @param Closure $view View getter.
     * 
     * @return ClosureContainer
     */
    public function helpers(Closure $view)
    {
        $finder = new Finder;
        $finder->addFilter(new ClassNameFilter(['prefix' => 'Helper\\']));
        $finder->addFilter(new ClassNameFilter(['prefix' => 'Europa\View\Helper\\']));
        
        // css
        $finder->config('Europa\View\Helper\Css', function() {
            return ['css'];
        });
        
        // js
        $finder->config('Europa\View\Helper\Js', function() {
            return ['js'];
        });
        
        // language configuration
        $finder->config('Europa\View\Helper\Lang', function() use ($view) {
            $locator = new Locator;
            $locator->addPath(__DIR__ . '/../../langs/en-us', 'ini');
            return [$view(), $locator];
        });
        
        return $finder;
    }
    
    /**
     * Returns the request.
     * 
     * @return RequestInterface
     */
    public function request()
    {
        return RequestAbstract::isCli() ? new Request\Cli : new Request\Http;
    }
    
    /**
     * Returns the response.
     * 
     * @return RequestInterface
     */
    public function response()
    {
        return RequestAbstract::isCli() ? new Response\Cli : new Response\Http;
    }
    
    /**
     * Returns the router.
     * 
     * @return Router
     */
    public function router()
    {
        $router = new Router;
        $router->setRoute('default', new RegexRoute(
            '(?<controller>[^.?]+)?',
            ':controller',
            ['controller' => 'index']
        ));
        
        return $router;
    }
    
    /**
     * Returns the view.
     * 
     * @return RequestInterface
     */
    public function view($request)
    {
        if ($request instanceof Request\Http && $suffix = $request->getUri()->getSuffix()) {
            return $this->get('view.http.' . $suffix);
        }
        return $this->get('view.php');
    }
    
    /**
     * Returns the CLI view.
     * 
     * @return Php
     */
    public function viewPhp($request, $helpers)
    {
        $interface = RequestAbstract::isCli() ? 'cli' : 'web';
        
        $locator = new Locator;
        $locator->addPath(__DIR__ . '/../../views');
        
        $view = new Php($locator);
        $view->setScript($interface . '/' . str_replace(' ', '/', $request->getParam('controller')));
        $view->setHelperContainer($helpers);
        
        return $view;
    }
    
    /**
     * Returns the JSON view.
     * 
     * @return Json
     */
    public function viewHttpJson()
    {
        return new Json;
    }
    
    /**
     * Returns the XML view.
     * 
     * @return Xml
     */
    public function viewHttpXml()
    {
        return new Xml;
    }
}