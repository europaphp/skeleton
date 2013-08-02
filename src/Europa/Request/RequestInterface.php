<?php

namespace Europa\Request;
use Serializable;

interface RequestInterface extends Serializable
{
  public function __toString();

  public function setMethod($method);

  public function getMethod();

  public function setParam($name, $value);

  public function getParam($name);
}