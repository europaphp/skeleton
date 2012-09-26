<?php

namespace Europa\Router\Provider;
use ArrayIterator;
use Europa\Router\TokenRoute;
use InvalidArgumentException;

/**
 * Reads an INI file and creates routes from it.
 * 
 * @category Routing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Ini implements ProviderInterface
{
    /**
     * The Ini file path.
     * 
     * @var string
     */
    private $file;
    
    /**
     * The route instances.
     * 
     * @var array
     */
    private $routes = [];
    
    /**
     * Constructs a new route INI file provider.
     * 
     * @param string $file The ini file.
     * 
     * @return Ini
     */
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
    
    /**
     * Sets the route factory to use for creating a route based on an ini name / value pair.
     * 
     * @param mixed $cb The callable factory. Anything that `is_callable()`.
     * 
     * @return Ini
     */
    public function setRouteFactory($cb)
    {
        if (!is_callable($cb)) {
            throw new InvalidArgumentException('The given route factory is not callable.');
        }
        
        $this->routeFactory = $cb;
        
        return $this;
    }
    
    /**
     * Returns the route factory.
     * 
     * @return mixed
     */
    public function getRouteFactory()
    {
        return $this->routeFactory;
    }
    
    /**
     * Returns an iterator of routes.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (!$this->routes) {
            $this->parse();
        }
        return new ArrayIterator($this->routes);
    }
    
    /**
     * Parses the ini file.
     * 
     * @return void
     */
    private function parse()
    {
        foreach (parse_ini_file($this->file) as $name => $value) {
            $this->routes[$name] = call_user_func($this->routeFactory, $name, $value);
        }
    }
}