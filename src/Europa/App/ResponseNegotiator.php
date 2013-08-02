<?php

namespace Europa\App;
use Europa\Request;
use Europa\Response;

class ResponseNegotiator
{
  private $suffixes = [];

  private $types = [];

  private $fallback;

  public function __invoke(Request\RequestInterface $request)
  {
    return $request instanceof Request\CliInterface
      ? $this->resolveCliResponse($request)
      : $this->resolveHttpResponse($request);
  }

  public function allow($type, $suffixes = [])
  {
    $this->types[$type] = $suffixes;

    foreach ((array) $suffixes as $suffix) {
      $this->suffixes[$suffix] = $type;
    }

    return $this;
  }

  public function fallback($type)
  {
    $this->fallback = $type;
    return $this;
  }

  private function resolveCliResponse(Request\CliInterface $request)
  {
    return new Response\Cli;
  }

  private function resolveHttpResponse(Request\HttpInterface $request)
  {
    if (isset($this->suffixes[$suffix = $request->getUri()->getSuffix()])) {
      $type = $this->suffixes[$suffix];
    } elseif (isset($this->types[$type = $request->accepts(array_keys($this->types))])) {
      $type = $this->types[$type];
    } else {
      $type = $this->fallback;
    }

    $response = new Response\Http;

    if ($type) {
      $response->setHeader('Content-Type', $type);
    }

    return $response;
  }
}