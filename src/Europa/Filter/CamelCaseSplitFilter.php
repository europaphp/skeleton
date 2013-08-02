<?php

namespace Europa\Filter;

class CamelCaseSplitFilter
{
  public function __invoke($value)
  {
    $parts = [''];

    foreach (str_split($value) as $char) {
      $lower = strtolower($char);

      if ($char === $lower) {
        $parts[count($parts) - 1] .= $lower;
      } else {
        $parts[] = $char;
      }
    }

    return array_filter($parts);
  }
}