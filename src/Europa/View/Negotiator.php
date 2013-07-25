<?php

namespace Europa\View;
use Europa\Request;

class Negotiator
{
    private $suffixMap = [];

    private $contentTypeMap = [];

    private $fallbackRenderer;

    public function __invoke(Request\RequestInterface $request)
    {
        if ($request instanceof Request\HttpInterface) {
            if ($suffix = $request->getUri()->getSuffix()) {
                $renderer = $this->suffixMap[$suffix];
            } elseif ($contentType = $request->accepts(array_keys($this->contentTypeMap))) {
                $renderer = $this->contentTypeMap[$contentType];
            }
        }

        if (!isset($renderer)) {
            $renderer = $this->fallbackRenderer;
        }

        if (!isset($renderer)) {
            throw new Exception\NotNegotiable(['request' => $request]);
        }

        return $renderer;
    }

    public function mapSuffix($suffix, callable $renderer)
    {
        $this->suffixMap[$suffix] = $renderer;
        return $this;
    }

    public function mapContentType($contentType, callable $renderer)
    {
        $this->contentTypeMap[$contentType] = $renderer;
        return $this;
    }

    public function otherwise(callable $fallbackRenderer)
    {
        $this->fallbackRenderer = $fallbackRenderer;
        return $this;
    }
}