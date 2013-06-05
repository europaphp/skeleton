<?php

namespace Europa\Router;
use Europa\Config\Config;
use Europa\Reflection\CallableReflector;
use Europa\Reflection\ClassReflector;
use Europa\Request\CliInterface;
use Europa\Request\HttpInterface;
use Europa\Request\RequestInterface;

class Router
{
    private $container;

    private $controllers = [];

    private $fallbackController;

    public function __construct(callable $container = null)
    {
        $this->container = $container;
    }

    public function __invoke(RequestInterface $request)
    {
        $call = null;
        $request = $this->translateRequest($request);

        foreach ($this->controllers as $pattern => $controller) {
            if (preg_match($pattern, $request, $params)) {
                $call = $controller;
                break;
            }
        }

        if (!$call) {
            if ($this->fallbackController) {
                $call = $this->fallbackController;
            } else {
                throw new Exception\ControllerNotFound('A route matching "%s" was unable to be found.', $request);
            }
        }

        $call = new CallableReflector($call);
        return $call->getReflector()->invokeArgs($params);
    }

    public function when($pattern, callable $controller)
    {
        $this->controllers[$pattern] = $controller;
        return $this;
    }

    public function otherwise(callable $controller)
    {
        $this->fallbackController = $controller;
        return $this;
    }

    public function import($routes)
    {
        foreach (new Config($routes) as $config) {
            $callable = $this->translateConfigToCallable($config);

            if ($config['when']) {
                $this->when($config['when'], $callable);
            } elseif ($config['else']) {
                $this->otherwise($config['else'], $callable);
            } else {
                throw new Exception\InvalidRouteConfiguration(
                    'No route pattern for "when" or "else" was specified.'
                );
            }
        }

        return $this;
    }

    private function translateConfigToCallable($config)
    {
        if (is_callable($config['call'])) {
            return $config['call'];
        }

        $class = null;
        $method = null;

        if (strpos($config['call'], '->')) {
            $parts = explode('->', $config['call']);
            $class = $parts[0];
            $method = $parts[1];
        }

        if ($config['inject']) {
            if (!$this->container) {
                throw new Exception\MustSpecifyContainer(
                    'Cannot inject "%s" to "%s" because no container was specified.',
                    implode($config['inject']),
                    $class
                );
            }

            $deps = [];

            foreach ($config['inject'] as $index => $dep) {
                $deps[$index] = call_user_func($this->container, $dep);
            }

            $class = (new ClassReflector($class))->newInstanceArgs($deps);
        } else {
            $class = new $class;
        }

        return [$class, $method];
    }

    private function translateRequest(RequestInterface $request)
    {
        if ($request instanceof CliInterface) {
            return $request->getCommand();
        }

        if ($request instanceof HttpInterface) {
            return $request->getMethod() . ' ' . $request->getUri()->getRequest();
        }

        throw new Exception\InvalidRequestInstance('Unable to translate request "%s" into a queryable string.', get_class($request));
    }
}