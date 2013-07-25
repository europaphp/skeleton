<?php

namespace Europa\Router;
use Europa\Di;
use Europa\Filter;
use Europa\Reflection;
use Europa\Request;
use Europa\Response;
use Europa\View;

class Router implements RouterInterface
{
    use Di\ContainerAware;

    private $route;

    private $routes = [];

    private $fallback;

    private $matcher;

    private $negotiator;

    private $response;

    private $viewScriptFilter;

    public function __construct()
    {
        $this->matcher = new Matcher\Regex;
        $this->negotiator = new View\Negotiator;
        $this->response = Response\ResponseAbstract::detect();
        $this->viewScriptFilter = [$this, 'defaultViewScriptFilter'];
    }

    public function __invoke(Request\RequestInterface $request)
    {
        $controller = $this->match($request);

        if ($controller === false) {
            return;
        }

        $controller = new Reflection\CallableReflector($controller);
        $params = $this->callController($controller, $request) ?: [];
        $view = call_user_func($this->negotiator, $request);

        if ($view instanceof View\ScriptAwareInterface && $instance = $controller->getInstance()) {
            $view->setScript(
                call_user_func(
                    $this->viewScriptFilter,
                    get_class($instance),
                    $controller->getReflector()->getName()
                )
            );
        }

        return $this->response->setBody($view($params));
    }

    public function getMatcher()
    {
        return $this->matcher;
    }

    public function setMatcher(callable $matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    public function getNegotiator()
    {
        return $this->negotiator;
    }

    public function setNegotiator(callable $negotiator)
    {
        $this->negotiator = $negotiator;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getViewScriptFilter()
    {
        return $this->viewScriptFilter;
    }

    public function setViewScriptFilter(callable $viewScriptFilter)
    {
        $this->viewScriptFilter = $viewScriptFilter;
        return $this;
    }

    public function when($pattern, $controller)
    {
        return $this->routes[$pattern] = $controller;
    }

    public function otherwise($controller)
    {
        return $this->fallback = $controller;
    }

    private function match(Request\RequestInterface $request)
    {
        $matcher = $this->matcher;

        foreach ($this->routes as $pattern => $controller) {
            if ($params = $matcher($pattern, $request) !== false) {
                $this->route = $pattern;
                $request->setParams($params);
                return $controller;
            }
        }

        return $this->fallback ?: false;
    }

    private function callController(Reflection\CallableReflector $controller, Request\RequestInterface $request)
    {
        $parameters = [];
        $container = $this->container;
        $instance = $controller->getInstance();

        // If the controller is an instance, we have already injected
        // dependencies into it's constructor. To give the user the most from
        // the router, we now inject named parameters into the method being
        // called.
        //
        // If the controller is just a callable, we simply inject dependencies
        // matching the parameter names into the callable since this is the
        // only chance it will have access to dependencies in the container.
        foreach ($controller->getReflector()->getParameters() as $parameter) {
            if ($instance) {
                if ($request->hasParam($parameter->getName())) {
                    $parameters[] = $request->getParam($parameter->getName());
                }
            } else {
                $parameters[] = $container($parameter->getName());
            }
        }

        return $controller->invokeArgs($parameters);
    }

    private function defaultViewScriptFilter($class, $method = null)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        if ($method) {
            $path .= DIRECTORY_SEPARATOR . $method;
        }

        return $path;
    }
}