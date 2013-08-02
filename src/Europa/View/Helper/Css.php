<?php

namespace Europa\View\Helper;
use Europa\Request\Uri as UriObject;

class Css
{
  const SUFFIX = '.css';

  public function compile($path, $xhtml = false)
  {
    $uri   = new UriObject($path . self::SUFFIX);
    $xhtml = $xhtml ? ' /' : '';
    return '<link rel="stylesheet" type="text/css" href="' . $uri . '"' . $xhtml . '>';
  }
}