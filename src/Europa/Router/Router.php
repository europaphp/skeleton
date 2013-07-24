<?php

namespace Europa\Router;
use Europa\Di;
use Europa\Reflection;
use Europa\Request;
use Europa\Response;
use Europa\View;

class Router implements RouterInterface
{
    use Di\ContainerAware;

    const REGEX_DELIMITER = '@';

    const REGEX_FLAGS = 'i';

    private $routes = [];

    private $fallback;

    private $negotiator;

    private $response;

    public function __construct()
    {
        $this->negotiator = new View\Negotiator;
        $this->response = Response\ResponseAbstract::detect();
    }

    public function __invoke(Request\RequestInterface $request)
    {
        if (!$controller = $this->match($request)) {
            return false;
        }

        $view = call_user_func($this->negotiator, $request);
        $this->response->setBody($view($controller($request)));

        return $this->response;
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

    public function when($pattern, callable $controller)
    {
        return $this->routes[$pattern] = $controller;
    }

    public function otherwise(callable $controller)
    {
        return $this->fallback = $controller;
    }

    private function match(Request\RequestInterface $request)
    {
        foreach ($this->routes as $pattern => $controller) {
            if (preg_match(self::REGEX_DELIMITER . $request . self::REGEX_DELIMITER . self::REGEX_FLAGS, $pattern, $params)) {
                $request->setParams($params);
                return $controller;
            }
        }

        return $this->fallback;
    }
}