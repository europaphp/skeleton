<?php

namespace Europa\App;

class ViewScriptFilter
{
  public function __invoke($class, $method = null)
  {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    if ($method) {
      $path .= DIRECTORY_SEPARATOR . $method;
    }

    return $path;
  }
}