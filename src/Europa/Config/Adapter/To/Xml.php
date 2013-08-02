<?php

namespace Europa\Config\Adapter\To;
use Europa\View;

class Xml
{
  public function __construct($config = [])
  {
    $this->view = new View\Xml($config);
  }

  public function __invoke($data)
  {
    return $this->view->render($data);
  }
}