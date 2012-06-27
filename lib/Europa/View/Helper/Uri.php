<?php

namespace Europa\View\Helper;
use Europa\Request;
use Europa\Router\RouterInterface;
use Exception;
use LogicException;

/**
 * A helper for formatting a passed in url.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Uri
{
    /**
     * The router to use.
     * 
     * @var RouterInterface
     */
    private $router;
    
    /**
     * Constructs the URI helper with the given router if specified.
     * 
     * @param RouterInterface $router The router to use.
     * 
     * @return Uri
     */
    public function __construct(RouterInterface $router = null)
    {
        $this->router = $router;
    }
    
    /**
     * @see self::current()
     */
    public function __toString()
    {
        return $this->current();
    }
    
    /**
     * Returns the current URI.
     * 
     * @return string
     */
    public function current()
    {
        return Request\Uri::detect()->__toString();
    }
    
    /**
     * Instantiates the url formatter and sets properties.
     * 
     * @param string $uri    The URI to normalize.
     * @param array  $params Parameters to apply to the URI.
     * 
     * @return Uri
     */
    public function format($uri = null, array $params = array())
    {
        $current = Request\Uri::detect();
        $current->setHost(null);
        $current->fromString($uri);
        $current->setParams($params);
        
        return $current->__toString();
    }

    /**
     * Returns whether the specified URI is the current URI.
     * 
     * @param string $uri    The URI to check.
     * @param array  $params Any params to use during the check.
     * 
     * @return bool
     */
    public function is($uri, array $params = array())
    {
        $current = Request\Uri::detect()->__toString();
        
        if ($this->router && $this->router->hasRoute($uri)) {
            $compare = $this->generate($uri, $params);
        } else {
            $compare = Request\Uri::detect()->fromString($uri)->setParams($params)->__toString();
        }
        
        return $current === $compare;
    }
    
    /**
     * Generates a URI for the specified route if it exists.
     * 
     * @param string $name   The name of the route.
     * @param array  $params The parameters to generate with.
     * 
     * @throws LogicException If a router isn't specified.
     * @throws LogicException If an exception is caught while generating.
     * 
     * @return string
     */
    public function generate($name, array $params = array())
    {
        if (!$this->router) {
            $class = get_class($this);
            throw new LogicException("You must configure {$class} with a router if calling generate.");
        }
        
        try {
            return $this->router->format($name, $params);
        } catch (Exception $e) {
            throw new LogicException("Could not generate a URI for {$name} with message: {$e->getMessage()}");
        }
    }
    
    /**
     * Redirects to the specified URL. Since views use output buffering headers should still be able to be sent.
     * 
     * @param string $uri The URI to redirect to.
     * 
     * @return void
     */
    public function redirect($uri)
    {
        (new Request\Uri($uri))->redirect();
    }
}