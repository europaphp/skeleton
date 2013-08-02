<?php

namespace Europa\View;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\ServiceContainerInterface;
use Europa\Router\Router;

class HelperConfiguration extends ConfigurationAbstract
{
  private $router;

  public function __construct(Router $router = null)
  {
    $this->router = $router;
  }

  public function capture()
  {
    return new Helper\Capture;
  }

  public function cli()
  {
    return new Helper\Cli;
  }

  public function css()
  {
    return new Helper\Css;
  }

  public function js()
  {
    return new Helper\Js;
  }

  public function json()
  {
    return new Helper\Json;
  }

  public function uri()
  {
    return new Helper\Uri($this->router);
  }
}