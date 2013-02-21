<?php

namespace Europa\Router;
use Europa\Config\Config;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use Europa\Request\CliInterface;
use Europa\Request\HttpInterface;
use Europa\Request\RequestInterface;

class Route implements RouteInterface
{
    const DEFAULT_CONTROLLER = 'index';

    const DEFAULT_ACTION = 'get';

    private $config = [
        'pattern'    => '',
        'format'     => ':controller/:action',
        'controller' => self::DEFAULT_CONTROLLER,
        'action'     => self::DEFAULT_ACTION
    ];

    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }

    public function query(RequestInterface $request)
    {
        $request = $this->translateRequest($request);
        $matched = @preg_match('!^' . $this->config['pattern'] . '$!i', $request, $matches);

        if ($matched === false) {
            Exception::toss('The route pattern "%s" is not valid because: ' . error_get_last()['message'], $this->config['pattern']);
        }

        if ($matched === 0) {
            return false;
        }

        $matches = $this->removeNumericallyIndexedParams($matches);

        return array_merge(
            ['controller' => $this->config['controller'], 'action' => $this->config['action']],
            $matches
        );
    }

    public function format(array $params = [])
    {
        $uri    = $this->config->format;
        $params = array_merge($this->config['defaults']->export(), $params);

        foreach ($params as $name => $value) {
            $uri = str_replace(':' . $name, $value);
        }

        return $uri;
    }

    private function translateRequest(RequestInterface $request)
    {
        if ($request instanceof CliInterface) {
            return $request->getCommand();
        }

        if ($request instanceof HttpInterface) {
            return $request->getMethod() . ' ' . $request->getUri()->getRequest();
        }

        Exception::toss('Unable to translate request "%s".', get_class($request));
    }

    private function removeNumericallyIndexedParams(array $params)
    {
        $filtered = [];

        foreach ($params as $name => $value) {
            if (!is_numeric($name)) {
                $filtered[$name] = $value;
            }
        }

        return $filtered;
    }
}