<?php

namespace Test\All\Router;
use Closure;
use Europa\Router\Adapter\Json;
use Europa\Router\Route;
use Europa\Router\Router;
use Exception;
use Testes\Test\UnitAbstract;

class RouterTest extends UnitAbstract
{
    public function manipulatingRoutes()
    {
        $router = new Router;
        
        $router['test1'] = [
            'request' => '^$',
            'params'  => ['controller' => 'index']
        ];

        $router['test2'] = new Route([
            'request' => 'test/:test',
            'params'  => ['controller' => 'test']
        ]);
        
        $this->assert($router['test1'] instanceof Route, 'First route was not bound.');
        $this->assert($router['test2'] instanceof Route, 'Second route was not bound.');
        
        unset($router['test1']);
        
        try {
            $router['test1'];
            $this->assert(false, 'First route was not removed.');
        } catch (Exception $e) {}
    }
    
    public function jsonRoutes()
    {
        $router = new Router;
        $router->import(__DIR__ . '/../../Provider/Router/routes.json');
        
        foreach ($routes as $name => $route) {
            $router[$name] = $route;
        }
        
        foreach ($router as $name => $route) {
            $this->assert($route instanceof Closure, 'The route should be an instance of "Closure".');
        }

        $this->assert($router('someroute'), 'Router should have matched query.');
    }

    public function uncallableRoute()
    {
        $router = new Router;

        try {
            $router['defalut'] = 'test-biatch';
            $this->assert(false, 'The router should have thrown an exception for the bad route.');
        } catch (Exception $e) {}
    }

    public function badProviderJsonFile()
    {
        try {
            new Json('oiasdiojgoi39j3jj93o39e');
            $this->assert(false, 'The INI provider should throw an exception if the file does not exist.');
        } catch (Exception $e) {}
    }
}