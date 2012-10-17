<?php

namespace Europa\Router;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Request\RequestInterface;

class Route
{
    private $config = [
        'uri'        => null,
        'methods'    => '*',
        'controller' => null
    ];

    public function __construct($config)
    {
        $this->config = new Config($this->config, $config);

        if (!$this->config->controller) {
            Exception::toss('The route "%s" did not provide a controller class name.', $this->config->expression);
        }
    }

    public function __invoke($name, RequestInterface $request)
    {
        if (!preg_match('!' . $this->config->uri . '!', $request->getUri()->getRequest(), $matches)) {
            return false;
        }

        if ($this->config->methods->count() && !in_array($request->getMethod(), $this->config->methods->values())) {
            return false;
        }

        if (!class_exists($this->config->controller)) {
            Exception::toss('The class "%s" given for route "%s" does not exist.', $this->config->controller, $name);
        }

        array_shift($matches);

        return new $this->config->controller($matches);
    }
}