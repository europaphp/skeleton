<?php

namespace Europa\Router;
use Europa\Config;
use Europa\Di;
use Europa\Reflection;

class RouterImporter implements Di\ContainerAwareInterface
{
    use Di\ContainerAware;

    public function __invoke(RouterInterface $router, $routes)
    {
        foreach (new Config\Config($routes) as $route) {
            if (!isset($route['call'])) {
                throw new Exception\InvalidRouteConfiguration('The route "%s" must specify a "call" option.', json_encode($route));
            }

            $call = $this->resolveCallable($route['call'], $router);

            if (isset($route['when'])) {
                $router->when($route['when'], $call);
            } elseif (isset($route['else'])) {
                $router->otherwise($route['else'], $call);
            } else {
                throw new Exception\InvalidRouteConfiguration('The route "%s" must specify either a "when" or "else" option.', json_encode($route));
            }
        }

        return $router;
    }

    private function resolveCallable($controller)
    {
        if (!is_callable($controller)) {
            if (strpos($controller, '->')) {
                $controller = $this->resolveCallableInstance($controller);
            } else {
                throw new Exception\RouteNotCallable('The route "%s" is not callable.', $controller);
            }
        }

        return $controller;
    }

    private function resolveCallableInstance($controller)
    {
        $args = [];
        $deps = $this->container;
        $parts = explode('->', $controller, 2);
        $class = new Reflection\ClassReflector($parts[0]);
        $method = $parts[1];

        if ($constructor = $class->getConstructor()) {
            foreach ($constructor->getParameters() as $parameter) {
                $args[] = $deps($parameter->getName());
            }
        }

        return [$class = $class->newInstanceArgs($args), $method];
    }
}