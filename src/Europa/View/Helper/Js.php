<?php

namespace Europa\View\Helper;
use Europa\Request\Uri as UriObject;

class Js
{
  const SUFFIX = '.js';

  public function compile($path, $xhtml = false)
  {
    $uri   = new UriObject($path . self::SUFFIX);
    $xhtml = $xhtml ? ' /' : '';
    return '<link rel="stylesheet" type="text/css" href="' . $uri . '"' . $xhtml . '>';
  }
}