<?php

namespace Europa\Router;
use Europa\Iterator\CallableIterator;
use Europa\Request\RequestInterface;
use Traversable;

class RouterArray
{
  private $routers;

  public function __construct(Traversable $routers)
  {
    $this->routers = new CallableIterator($routers);
  }

  public function __invoke(RequestInterface $request)
  {
    foreach ($this->routers as $router) {
      if ($response = $router($request)) {
        return $response;
      }
    }
  }
}