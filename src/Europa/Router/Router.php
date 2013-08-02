<?php

namespace Europa\Router;
use Europa\Filter;
use Europa\Reflection;
use Europa\Request;
use Europa\Response;
use Europa\View;

class Router implements RouterInterface
{
  private $fallback;

  private $matcher;

  private $route;

  private $routes = [];

  public function __construct()
  {
    $this->matcher = new Matcher\Regex;
  }

  public function __invoke(Request\RequestInterface $request)
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

  public function when($pattern, $controller)
  {
    return $this->routes[$pattern] = $controller;
  }

  public function otherwise($controller)
  {
    return $this->fallback = $controller;
  }

  public function route()
  {
    return $this->route;
  }

  public function routes()
  {
    return $this->routes;
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
}