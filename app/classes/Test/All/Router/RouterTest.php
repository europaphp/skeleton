<?php

namespace Test\All\Router;
use Europa\Router\Provider\Ini;
use Europa\Router\Router;
use Europa\Router\RegexRoute;
use Europa\Router\TokenRoute;
use Exception;
use Testes\Test\UnitAbstract;

class RouterTest extends UnitAbstract
{
    public function manipulatingRoutes()
    {
        $router = new Router;
        $router->setRoute('test1', new RegexRoute('/^$/', '', ['controller' => 'index']));
        $router->setRoute('test2', new TokenRoute('test/:test', ['controller' => 'test']));
        
        $this->assert($router->getRoute('test1') instanceof RegexRoute, 'First route was not bound.');
        $this->assert($router->getRoute('test2') instanceof TokenRoute, 'Second route was not bound.');
        $this->assert($router->format('test1') === '', 'First route was not formatted.');
        $this->assert($router->format('test2', ['test' => 'gaga']) === 'test/gaga', 'Second route was not formatted.');
        
        $router->removeRoute('test1');
        
        try {
            $router->getRoute('test1');
            $this->assert(!$router->hasRoute('test1'), 'First route was not removed.');
        } catch (Exception $e) {}
        
        $router->removeRoutes();
        
        try {
            $router->getRoute('test2');
            $this->assert(!$router->hasRoute('test2'), 'Second route was not removed when removing all routes.');
        } catch (Exception $e) {}
    }
    
    public function iniRoutes()
    {
        $router = new Router;
        $router->setRoutes(new Ini(__DIR__ . '/../../Provider/Router/routes.ini'));
        
        $this->assert($router->hasRoute('test1'), 'First route was not bound.');
        $this->assert($router->hasRoute('test2'), 'Second route was not bound.');
        $this->assert($router->format('test1', ['test' => 1]) === 'test1/1', 'First route was not formatted.');
        $this->assert($router->format('test2', ['test' => 2]) === 'test2/2', 'Second route was not formatted.');
    }

    public function tokenRoutes()
    {
        $route = new TokenRoute('my/route/:param1/(:param2)/(:param3)/test');

        $result = $route->query('my/route/value1/test');
        $this->assert($result && $result['param1'] === 'value1');

        $result = $route->query('my/route/value1/value2/test');
        $this->assert($result && $result['param1'] === 'value1' && $result['param2'] === 'value2');

        $result = $route->query('my/route/value1/value2/value3/test');
        $this->assert($result && $result['param1'] === 'value1' && $result['param2'] === 'value2' && $result['param3'] === 'value3');

        $this->assert($route->query('my/route/value1/value2/value3/test.json'));
        $this->assert(!$route->query('my/route/test'));
        $this->assert(!$route->query('my/route/value1'));
        $this->assert(!$route->query('my/route/value1/'));
        $this->assert(!$route->query('my/route/value1/tes'));
        $this->assert(!$route->query('my/route/value1/tests'));

        // test for trailing forward slashes
        $this->assert($route->query('my/route/value1/test/'));
        $this->assert(!$route->query('my/route/value1/tes/'));
        $this->assert(!$route->query('my/route/value1/tests/'));

        // test for suffixes
        $this->assert($route->query('my/route/value1/test.json'));
        $this->assert(!$route->query('my/route/value1/tes.xml'));
        $this->assert(!$route->query('my/route/value1/tests.html'));
    }
}