<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Request\Http;
use Europa\Request\RequestInterface;

class Negotiator
{
    /**
     * Maps a request content type to the method that returns its view.
     * 
     * @var array
     */
    private $config = [
        'jsonpParam' => 'callback',
        'suffixMap' => [
            'json' => 'resolveJson',
            'xml'  => 'resolveXml'
        ],
        'typeMap' => [
            'application/json'       => 'resolveJson',
            'application/javascript' => 'resolveJson',
            'text/xml'               => 'resolveXml'
        ]
    ];

    /**
     * The filter that is used to return the appropriate view script using the supplied request.
     * 
     * @var callable
     */
    private $viewScriptFilter;

    /**
     * Sets up a new negotiator.
     * 
     * @var mixed $config The negotiator configuration.
     * 
     * @return Negotiator
     */
    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
        $this->viewScriptFilter = [$this, 'viewScriptFilter'];
    }

    /**
     * Decides what type of view to return based on the specified request.
     * 
     * @param RequestInterface $request The request to use.
     * 
     * @return ViewInterface
     */
    public function __invoke(RequestInterface $request)
    {
        $view = null;

        // We only negotiate a content type if the request is using Http.
        if ($request instanceof Http) {
            $method = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $request->getUri()->getSuffix()) {
                $method = $this->config->suffixMap[$suffix];
            } elseif ($type = $request->accepts(array_keys($this->config->typeMap->export()))) {
                $method = $this->config->typeMap[$type];
            }

            // Only render a different view if one exists.
            if ($method && method_exists($this, $method)) {
                $view = $this->$method($request);
            }
        }

        // Default to using a PHP view.
        if (!$view) {
            $view = $this->resolvePhp($request);
        }

        return $view;
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
        if ($callback = $request->getParam($this->config->jsonpParam)) {
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
        return new Php($this->config->phpView);
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