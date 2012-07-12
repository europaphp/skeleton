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
     * @param string $uri    The URI to normalise.
     * @param array  $params Parameters to apply to the URI.
     * 
     * @return Uri
     */
    public function format($uri = null, array $params = [])
    {
        $obj = Request\Uri::detect();
        
        if ($uri && $this->router && $this->router->hasRoute($uri)) {
            $obj->setRequest($this->router->format($uri, $params));
        } else {
            $obj->setRequest($uri)->setParams($params);
        }
        
        return (string) $obj;
    }

    /**
     * Returns whether the specified URI is the current URI.
     * 
     * @param string $uri    The URI to check.
     * @param array  $params Any params to use during the check.
     * 
     * @return bool
     */
    public function is($uri, array $params = [])
    {
        return $this->current() === $this->format($uri, $params);
    }
    
    /**
     * Redirects to the specified URL. Since views use output buffering headers should still be able to be sent.
     * 
     * @param string $uri The URI to redirect to.
     * 
     * @return void
     */
    public function redirect($uri = null, array $params = [])
    {
        if (headers_sent()) {
            throw new LogicException(sprintf('Cannot redirect to "%s" from the view because output has already started.', $uri));
        }
        
        (new Request\Uri($this->format($uri, $params)))->redirect();
    }
}