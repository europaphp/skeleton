<?php

namespace Europa\View\Helper;
use Europa\Exception\Exception;
use Europa\Request\Uri as RequestUri;
use Europa\Router\Route;
use Europa\Router\Router;

class Uri
{
  private $router;

  public function __construct(Router $router = null)
  {
    $this->router = $router;
  }

  public function __toString()
  {
    return $this->current();
  }

  public function current()
  {
    return RequestUri::detect()->__toString();
  }

  public function format($uri = null, array $params = [])
  {
    $obj = RequestUri::detect();

    if ($uri && $this->router) {
      $obj->setRequest($this->router->format($uri, $params));
    } else {
      $obj->setRequest($uri)->setParams($params);
    }

    return (string) $obj;
  }

  public function is($uri, array $params = [])
  {
    return $this->current() === $this->format($uri, $params);
  }

  public function redirect($uri = null, array $params = [])
  {
    if (headers_sent()) {
      Exception::toss('Cannot redirect to "%s" from the view because output has already started.', $uri);
    }

    (new RequestUri($this->format($uri, $params)))->redirect();
  }
}