<?php

namespace Europa\Request;

interface RequestAwareInterface
{
  public function getRequest();

  public function setRequest(RequestInterface $request);
}