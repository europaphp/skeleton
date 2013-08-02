<?php

namespace Europa\Router\Matcher;
use Europa\Request;

class Regex
{
  const DELIMITER = '@';

  const FLAGS = 'i';

  private $delimiter;

  private $flags;

  public function __construct()
  {
    $this->delimiter = self::DELIMITER;
    $this->flags = self::FLAGS;
  }

  public function __invoke($pattern, Request\RequestInterface $request)
  {
    if (preg_match($this->delimiter . '^' . $pattern . '$' . $this->delimiter . $this->flags, $request, $params)) {
      array_shift($params);
      return $params;
    }

    return false;
  }

  public function getDelimiter()
  {
    return $this->delimiter;
  }

  public function setDelimiter($delimiter)
  {
    $this->delimiter = $delimiter;
    return $this;
  }

  public function getFlags()
  {
    return $this->flags;
  }

  public function setFlags($flags)
  {
    $this->flags = $flags;
    return $this;
  }
}