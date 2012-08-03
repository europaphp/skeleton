<?php

namespace Europa\Router\Provider;
use ArrayIterator;
use Europa\Router\TokenRoute;
use InvalidArgumentException;

class Ini implements ProviderInterface
{
    private $file;
    
    private $routes = [];
    
    public function __construct($file)
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                'The file "%s" is not a valid routes file.',
                $file
            ));
        }
        
        $this->file         = $file;
        $this->routeFactory = function($name, $value) {
            return new TokenRoute($value, ['controller' => $name]);
        };
    }
    
    public function setRouteFactory($cb)
    {
        if (!is_callable($cb)) {
            throw new InvalidArgumentException('The given route factory is not callable.');
        }
        
        $this->routeFactory = $cb;
        
        return $this;
    }
    
    public function getRouteFactory()
    {
        return $this->routeFactory;
    }
    
    public function getIterator()
    {
        if (!$this->routes) {
            $this->parse();
        }
        return new ArrayIterator($this->routes);
    }
    
    private function parse()
    {
        foreach (parse_ini_file($this->file) as $name => $value) {
            $this->routes[$name] = call_user_func($this->routeFactory, $name, $value);
        }
    }
}