<?php

namespace Europa\Router;
use Europa\Di;

interface RouterInterface
{
  public function when($pattern, $controller);

  public function otherwise($controller);

  public function route();

  public function routes();
}