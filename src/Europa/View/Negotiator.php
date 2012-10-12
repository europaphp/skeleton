<?php

namespace Europa\View;
use Europa\Request\Http;
use Europa\Request\RequestInterface;

class Negotiator
{
    /**
     * The name of the JSONP callback parameter in the request.
     * 
     * @var string
     */
    private $callback = 'callback';

    /**
     * Maps a request URI suffix to the method that returns its view.
     * 
     * @var array
     */
    private $suffixMap = [
        'json' => 'resolveJson',
        'xml'  => 'resolveXml'
    ];

    /**
     * Maps a request content type to the method that returns its view.
     * 
     * @var array
     */
    private $typeMap = [
        'application/json'       => 'resolveJson',
        'application/javascript' => 'resolveJson',
        'text/xml'               => 'resolveXml'
    ];

    /**
     * Decides what type of view to return based on the specified request.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return ViewInterface
     */
    public function __invoke(RequestInterface $request)
    {
        // We only negotiate a content type if the request is using Http.
        if ($container->request instanceof Http) {
            $method = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $container->request->getUri()->getSuffix()) {
                $method = $this->suffixMap[$suffix];
            } elseif ($type = $container->request->accepts(array_keys($this->typeMap))) {
                $method = $this->typeMap[$type];
            }

            // Only render a different view if one exists.
            if ($method && method_exists($this, $method)) {
                return $this->$method($request);
            }
        }

        // Default to using a PHP view.
        return $this->resolvePhp($request);
    }

    /**
     * Returns a `Json` or `Jsonp` view.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return Json | Jsonp
     */
    private function resolveJson($request)
    {
        if ($callback = $request->getParam($this->callback)) {
            return new Jsonp($callback);
        }

        return new Json;
    }

    /**
     * Returns the `Php` view.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return Php
     */
    private function resolvePhp($request)
    {
        return new Php;
    }

    /**
     * Returns the `Xml` view.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return Xml
     */
    private function resolveXml($request)
    {
        return new Xml;
    }
}