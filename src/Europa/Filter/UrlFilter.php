<?php

namespace Europa\Filter;

class UrlFilter
{
  public function __invoke($value)
  {
    $value = (new ClassNameFilter)->__invoke($value);
    $value = str_replace('\\', '/', $value);
    $value = (new CamelCaseSplitFilter)->__invoke($value);
    $value = array_map('strtolower', $value);
    $value = implode('-', $value);
    $value = str_replace('/-', '/', $value);
    return trim($value, '-');
  }
}