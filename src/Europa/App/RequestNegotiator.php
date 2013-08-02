<?php

namespace Europa\App;
use Europa\Request;

class RequestNegotiator
{
  public function __invoke()
  {
    if (PHP_SAPI === 'cli') {
      return new Request\Cli;
    }

    return new Request\Http;
  }
}