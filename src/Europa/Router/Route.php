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
    const DEFAULT_CONTROLLER = 'index';

    const DEFAULT_ACTION = 'get';

    const MATCHER_CONTAINS = 'contains';

    const MATCHER_EXACT = 'exact';

    const MATCHER_REGEX = 'regex';

    const PARAM_CONTROLLER = 'controller';

    const PARAM_ACTION = 'action';

    private $config = [
        'matcher'          => self::MATCHER_REGEX,
        'pattern'          => '',
        'format'           => ':controller/:action',
        'controller'       => self::DEFAULT_CONTROLLER,
        'action'           => self::DEFAULT_ACTION,
        'controllerPrefix' => 'Controller\\',
        'controllerSuffix' => ''
    ];

    private static $matchers = [
        self::MATCHER_CONTAINS => ['Europa\Router\Route', 'matchContains'],
        self::MATCHER_EXACT    => ['Europa\Router\Route', 'matchExact'],
        self::MATCHER_REGEX    => ['Europa\Router\Route', 'matchRegex']
    ];

    private static $translators = [
        'Europa\Request\Cli'  => ['Europa\Router\Route', 'translateCli'],
        'Europa\Request\Http' => ['Europa\Router\Route', 'translateHttp']
    ];

    public function __construct($config)
    {
        $this->config = new Config($this->config, $config);
    }

    public function __invoke($name, RequestInterface $request)
    {
        $matches = $this->match($request);

        if ($matches === false) {
            return false;
        }

        $request->setParam(self::PARAM_CONTROLLER, $this->config->controller);
        $request->setParam(self::PARAM_ACTION, $this->config->action);
        $request->setParams($matches);

        if (class_exists($controller = $this->resolve($request))) {
            return new $controller;
        }

        Exception::toss('The controller class "%s" given for route "%s" does not exist.', $controller, $name);
    }

    public function format(array $params = [])
    {
        $uri    = $this->config->format;
        $params = array_merge($this->config->defaults->export(), $params);

        foreach ($params as $name => $value) {
            $uri = str_replace(':' . $name, $value);
        }

        return $uri;
    }

    private function match(RequestInterface $request)
    {
        $request = $this->translate($request);
        $matcher = $this->getMatcher($this->config->matcher);
        return $matcher($this->config->pattern, $request);
    }

    private function translate(RequestInterface $request)
    {
        $translator = $this->getTranslator(get_class($request));
        return strtolower($translator($request));
    }

    private function resolve(RequestInterface $request)
    {
        $filter = new ClassNameFilter([
            'prefix' => $this->config->controllerPrefix,
            'suffix' => $this->config->controllerSuffix
        ]);

        return $filter->__invoke($request->getParam(self::PARAM_CONTROLLER));
    }

    private static function matchRegex($pattern, $query)
    {
        if (!$pattern) {
            return false;
        }

        $matched = @preg_match('!^' . $pattern . '$!i', $query, $matches);

        if ($matched === false) {
            Exception::toss('The route pattern "%s" is not valid.', $pattern);
        }

        if ($matched) {
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    private static function matchContains($pattern, $query)
    {
        return stripos($query, $pattern) ? [] : false;
    }

    private static function matchExact($pattern, $query)
    {
        return strtolower($pattern) === $query ? [] : false;
    }

    private static function translateCli(CliInterface $request)
    {
        return $request->getCommand();
    }

    private static function translateHttp(HttpInterface $request)
    {
        return $request->getMethod() . ' ' . $request->getUri()->getRequest();
    }

    public static function setMatcher($option, callable $matcher)
    {
        self::$matchers[$option] = $matcher;
        return $this;
    }

    public static function getMatcher($option)
    {
        if (isset(self::$matchers[$option])) {
            return self::$matchers[$option];
        }

        Exception::toss('There is no matcher for configuration option "%s".', $option);
    }

    public static function hasMatcher($option)
    {
        return isset(self::$matchers[$option]);
    }

    public static function removeMatcher($option)
    {
        if (isset(self::$matchers[$option])) {
            unset(self::$matchers[$option]);
        }
    }

    public static function setTranslator($className, callable $transformer)
    {
        self::$translators[$className] = $transformer;
    }

    public static function getTranslator($className)
    {
        if (isset(self::$translators[$className])) {
            return self::$translators[$className];
        }

        Exception::toss('There is no request translator for a request class of "%s".', $className);
    }

    public static function hasTranslator($className)
    {
        return isset(self::$translators[$className]);
    }

    public static function removeTranslator($className)
    {
        if (isset(self::$translators[$className])) {
            unset(self::$translators[$className]);
        }
    }
}