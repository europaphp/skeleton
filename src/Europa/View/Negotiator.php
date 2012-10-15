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
        'actionParamName'        => 'action',
        'controllerParamName'    => 'controller',
        'jsonpCallbackParamName' => 'callback',
        'phpView'                => [
            'helper.filter' => []
        ],
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
        // We only negotiate a content type if the request is using Http.
        if ($container->request instanceof Http) {
            $method = null;

            // Specifying a suffix overrides the Accept header.
            if ($suffix = $container->request->getUri()->getSuffix()) {
                $method = $this->config->suffixMap[$suffix];
            } elseif ($type = $container->request->accepts(array_keys($this->config->typeMap->export()))) {
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

        // Sets the view script based on the request.
        if ($view instanceof ViewScriptInterface) {
            $view->setScript(call_user_func($this->viewScriptFilter, $request));
        }

        return $view;
    }

    /**
     * Sets the filter used to return the path to the view script.
     * 
     * @param callable $viewScriptFilter The view script filter.
     * 
     * @return Negotiator
     */
    public function setViewScriptFilter(callable $viewScriptFilter)
    {
        $this->viewScriptFilter = $viewScriptFilter;
        return $this;
    }

    /**
     * Returns the view script filter.
     * 
     * @return callable
     */
    public function getViewScriptFilter()
    {
        return $this->viewScriptFilter;
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

    /**
     * The default view script filter.
     * 
     * @param RequestInterface $request The request to use to resolve the correct view script.
     * 
     * @return string
     */
    private function viewScriptFilter(RequestInterface $request)
    {
        return ($request->isCli() ? 'cli' : 'web')
            . '/'
            . $request->getParam($this->config->controllerParamName)
            . '/'
            . $request->getParam($this->config->actionParamName);
    }
}