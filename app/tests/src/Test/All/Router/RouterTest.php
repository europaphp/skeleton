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
    
    public function json()
    {
        $router = new Router;
        $router->import(__DIR__ . '/../../Provider/Router/routes.json');

        if (!$router->count()) {
            $this->assert(false, 'No routes were imported.');
        }
        
        foreach ($router as $name => $route) {
            $this->assert(is_callable($route), 'The route should be callable.');
            $this->assert($route instanceof Route, 'The route should be an instance of "Europa\Router\Route".');
        }
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