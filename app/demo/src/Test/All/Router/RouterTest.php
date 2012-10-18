<?php

namespace Test\All\Router;
use Closure;
use Europa\Router\Adapter\Ini;
use Europa\Router\Router;
use Europa\Router\Route\Regex;
use Europa\Router\Route\Token;
use Exception;
use Testes\Test\UnitAbstract;

class RouterTest extends UnitAbstract
{
    public function manipulatingRoutes()
    {
        $router = new Router;
        $router['test1'] = new Regex('/^$/', ['controller' => 'index']);
        $router['test2'] = new Token('test/:test', ['controller' => 'test']);
        
        $this->assert($router['test1'] instanceof Regex, 'First route was not bound.');
        $this->assert($router['test2'] instanceof Token, 'Second route was not bound.');
        
        unset($router['test1']);
        
        try {
            $router['test1'];
            $this->assert(false, 'First route was not removed.');
        } catch (Exception $e) {}
    }
    
    public function iniRoutes()
    {
        $router = new Router;
        $routes = new Ini(__DIR__ . '/../../Provider/Router/routes.ini');
        
        foreach ($routes as $name => $route) {
            $router[$name] = $route;
        }
        
        foreach ($router as $name => $route) {
            $this->assert($route instanceof Closure, 'The route should be an instance of "Closure".');
        }

        $this->assert($router('someroute'), 'Router should have matched query.');
    }

    public function Tokens()
    {
        $route = new Token('my/route/:param1/(:param2)/(:param3)/test');

        $result = $route('my/route/value1/test');
        $this->assert($result && $result['param1'] === 'value1');

        $result = $route('my/route/value1/value2/test');
        $this->assert($result && $result['param1'] === 'value1' && $result['param2'] === 'value2');

        $result = $route('my/route/value1/value2/value3/test');
        $this->assert($result && $result['param1'] === 'value1' && $result['param2'] === 'value2' && $result['param3'] === 'value3');

        $this->assert($route('my/route/value1/value2/value3/test.json'));
        $this->assert(!$route('my/route/test'));
        $this->assert(!$route('my/route/value1'));
        $this->assert(!$route('my/route/value1/'));
        $this->assert(!$route('my/route/value1/tes'));
        $this->assert(!$route('my/route/value1/tests'));

        // test for trailing forward slashes
        $this->assert($route('my/route/value1/test/'));
        $this->assert(!$route('my/route/value1/tes/'));
        $this->assert(!$route('my/route/value1/tests/'));

        // test for suffixes
        $this->assert($route('my/route/value1/test.json'));
        $this->assert(!$route('my/route/value1/tes.xml'));
        $this->assert(!$route('my/route/value1/tests.html'));
    }

    public function uncallableRoute()
    {
        $router = new Router;

        try {
            $router['defalut'] = 'test-biatch';
            $this->assert(false, 'The router should have thrown an exception for the bad route.');
        } catch (Exception $e) {}
    }

    public function badProviderIniFile()
    {
        try {
            new Ini('oiasdiojgoi39j3jj93o39e');
            $this->assert(false, 'The INI provider should throw an exception if the file does not exist.');
        } catch (Exception $e) {}
    }
}