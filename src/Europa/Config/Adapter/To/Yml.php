<?php

namespace Europa\Config\Adapter\To;
use Europa\Exception\Exception;

class Yml
{
  public function __construct()
  {
    if (!function_exists('yaml_emit')) {
      Exception::toss('In order to use the YAML config adapter you must install the PECL YAML extension. See http://php.net/yaml for more information.');
    }
  }

  public function __invoke($data)
  {
    set_error_handler($this->generateErrorHandler($data));

    $parsed = yaml_emit($data);

    restore_error_handler();

    return $parsed;
  }

  private function generateErrorHandler($data)
  {
    return function($errno, $errstr, $errfile, $errline, $errcontext) use ($data) {
      Exception::toss('Unable to parse YAML string "%s" with error: %s', $data, $errstr);
    };
  }
}