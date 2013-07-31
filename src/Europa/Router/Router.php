<?php

namespace Europa\Router;
use Europa\Filter;
use Europa\Reflection;
use Europa\Request;
use Europa\Response;
use Europa\View;

class Router implements RouterInterface
{
    private $caller;

    private $fallback;

    private $matcher;

    private $resolver;

    private $responseNegotiator;

    private $route;

    private $routes = [];

    private $viewNegotiator;

    private $viewScriptFilter;

    public function __construct()
    {
        $this->caller = new Caller;
        $this->matcher = new Matcher\Regex;
        $this->resolver = new Resolver;
        $this->response = Response\ResponseAbstract::detect();
        $this->responseNegotiator = new ResponseNegotiator;
        $this->viewNegotiator = new ViewNegotiator;
        $this->viewScriptFilter = [$this, 'defaultViewScriptFilter'];
    }

    public function __invoke(Request\RequestInterface $request)
    {
        $controller = $this->match($request);

        if ($controller === false) {
            return;
        }

        if (!$controller = call_user_func($this->resolver, $controller, $request)) {
            throw new Exception\RouteNotCallable(['route' => $this->route]);
        }

        $args = call_user_func($this->caller, $controller, $request);
        $view = call_user_func($this->viewNegotiator, $request);
        $resp = call_user_func($this->responseNegotiator, $request);
        $this->applyViewScriptIfApplicable($view, $controller);

        return $resp->setBody($view($args));
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function setCaller(callable $caller)
    {
        $this->caller = $caller;
        return $this;
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

    public function getResolver()
    {
        return $this->resolver;
    }

    public function setResolver(callable $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    public function getResponseNegotiator()
    {
        return $this->responseNegotiator;
    }

    public function setResponseNegotiator(callable $negotiator)
    {
        $this->responseNegotiator = $negotiator;
        return $this;
    }

    public function getViewNegotiator()
    {
        return $this->viewNegotiator;
    }

    public function setViewNegotiator(callable $negotiator)
    {
        $this->viewNegotiator = $negotiator;
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

    private function defaultViewScriptFilter($class, $method = null)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        if ($method) {
            $path .= DIRECTORY_SEPARATOR . $method;
        }

        return $path;
    }

    private function applyViewScriptIfApplicable(callable $view, callable $controller)
    {
        $controller = new Reflection\CallableReflector($controller);

        if ($view instanceof View\ScriptAwareInterface && $instance = $controller->getInstance()) {
            $view->setScript(
                call_user_func(
                    $this->viewScriptFilter,
                    get_class($instance),
                    $controller->getReflector()->getName()
                )
            );
        }
    }
}