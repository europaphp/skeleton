<?php

namespace Europa\Request;

trait RequestAware
{
  private $request;

  public function getRequest()
  {
    return $this->request;
  }

  public function setRequest(RequestInterface $request)
  {
    $this->request = $request;
    return $this;
  }
}