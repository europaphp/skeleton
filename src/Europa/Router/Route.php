<?php

namespace Europa\Router;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use Europa\Request\CliInterface;
use Europa\Request\HttpInterface;
use Europa\Request\RequestInterface;

class Route
{
    const CONTROLLER = 'controller';

    private $config = [
        'request'           => '',
        'method'            => 'get',
        'format'            => ':controller/:action',
        'params'            => ['controller' => 'index', 'action' => 'get'],
        'controller.prefix' => 'Controller\\',
        'controller.suffix' => ''
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
        // Guilty until proven innocent.
        $matches = false;

        // Allow both HTTP and CLI requests to be routed.
        if ($request instanceof HttpInterface) {
            $matches = $this->handleHttpRequest($request);
        } elseif ($request instanceof CliInterface) {
            $matches = $this->handleCliRequest($request);
        }

        // If nothing was matched, the route failed.
        if (!$matches) {
            return false;
        }

        // The first match is the whole request; we don't use this.
        array_shift($matches);

        // Set defaults and matches from the route expression.
        $request->setParams($this->config->params);
        $request->setParams($matches);

        // A specified controller class overrides the "controller" parameter in the request.
        $controller = $this->resolveController($request);
        
        // Ensure the controller exists.
        if (!class_exists($controller)) {
            Exception::toss('The controller class "%s" given for route "%s" does not exist.', $controller, $name);
        }

        return new $controller;
    }

    /**
     * Allows the route to be reverse engineered. Good if you don't want to have to re-write your URLs if you update your routes.
     * 
     * @param array $params The parameters to format with.
     * 
     * @return string.
     */
    public function format(array $params = [])
    {
        $uri    = $this->config->format;
        $params = array_merge($this->config->defaults->export(), $params);

        foreach ($params as $name => $value) {
            $uri = str_replace(':' . $name, $value);
        }

        return $uri;
    }

    /**
     * Handles a request using the HTTP interface.
     * 
     * @param RequestInterface $request The request being matched.
     * 
     * @return array | false
     */
    private function handleHttpRequest(HttpInterface $request)
    {
        if ($this->config->method !== $request->getMethod()) {
            return false;
        }

        if (!preg_match('!' . $this->config->request . '!', $request->getUri()->getRequest(), $matches)) {
            return false;
        }

        return $matches;
    }

    /**
     * Handles a request using the CLI interface.
     * 
     * @param RequestInterface $request The request being matched.
     * 
     * @return array | false
     */
    private function handleCliRequest(CliInterface $request)
    {
        if (!$this->config->request) {
            return false;
        }

        if ($this->config->method !== 'cli') {
            return false;
        }

        if (!preg_match('!' . $this->config->request . '!', $request->getCommand(), $matches)) {
            return false;
        }

        return $matches;
    }

    /**
     * Formats a controller name from the request if no "controller" configuration option is specified.
     * 
     * @param RequestInterface $request The request to resolve the controller from if none is specified in the config.
     * 
     * @return string
     */
    private function resolveController(RequestInterface $request)
    {
        return call_user_func(new ClassNameFilter($this->config->controller), $request->getParam(self::CONTROLLER));
    }
}