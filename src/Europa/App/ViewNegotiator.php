<?php

namespace Europa\App;
use Europa\Reflection;
use Europa\Request;
use Europa\View;

class ViewNegotiator
{
  private $suffixes = [];

  private $types = [];

  private $fallback;

  public function __invoke(Request\RequestInterface $request)
  {
    if ($request instanceof Request\HttpInterface) {
      if (isset($this->types[$type = $request->getUri()->getSuffix()])) {
        $renderer = $this->types[$type];
      } elseif (isset($this->types[$type = $request->accepts(array_keys($this->types))])) {
        $renderer = $this->types[$type];
      }
    }

    if (!isset($renderer)) {
      $renderer = $this->fallback;
    }

    if (!isset($renderer)) {
      throw new Exception\NotNegotiable(['request' => $request]);
    }

    return $renderer;
  }

  public function map($maps, callable $view)
  {
    foreach ((array) $maps as $map) {
      $this->types[$map] = $view;
    }

    return $this;
  }

  public function fallback(callable $fallback)
  {
    $this->fallback = $fallback;
    return $this;
  }
}