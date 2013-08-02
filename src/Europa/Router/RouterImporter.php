<?php

namespace Europa\Router;
use Europa\Config;
use Europa\Reflection;

class RouterImporter
{
  public function __invoke(RouterInterface $router, $routes)
  {
    foreach (new Config\Config($routes) as $route) {
      if (!isset($route['call'])) {
        throw new Exception\InvalidRouteConfiguration(['route' => json_encode($route)]);
      }

      if (isset($route['when'])) {
        $router->when($route['when'], $route['call']);
      } elseif (isset($route['else'])) {
        $router->otherwise($route['else'], $route['call']);
      } else {
        throw new Exception\InvalidRouteConfiguration(['route' => json_encode($route)]);
      }
    }

    return $router;
  }
}