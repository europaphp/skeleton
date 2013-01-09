<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Request\Http;
use Europa\Request\RequestInterface;

class Negotiator
{
    private $config = [
        'jsonpParam' => 'callback',
        'xmlConfig'  => [],
        'suffixMap'   => [
            'json' => 'resolveJson',
            'xml'  => 'resolveXml'
        ],
        'typeMap' => [
            'application/json'       => 'resolveJson',
            'application/javascript' => 'resolveJson',
            'text/xml'               => 'resolveXml'
        ]
    ];

    private $viewScriptFilter;

    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
        $this->viewScriptFilter = [$this, 'viewScriptFilter'];
    }

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

    private function resolveJson($request)
    {
        if ($callback = $request->getParam($this->config->jsonpParam)) {
            return new Jsonp($callback);
        }

        return new Json;
    }

    private function resolvePhp($request)
    {
        return new Php($this->config->phpView);
    }

    private function resolveXml($request)
    {
        return new Xml($this->config->xmlConfig);
    }
}