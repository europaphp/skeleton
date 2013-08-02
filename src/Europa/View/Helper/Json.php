<?php

namespace Europa\View\Helper;

class Json
{
  public function compile(array $vars, $ns = null)
  {
    $js = '';
    $ns = 'window';

    if ($ns) {
      foreach (explode('.', $ns) as $subNs) {
        $ns .= '[' . $subNs . ']';
        $js .= $ns . ' = {};' . PHP_EOL;
      }
    }

    foreach ($vars as $name => $value) {
      $js .= $ns . '[' . json_encode($name) . '] = ' . $this->toJson($value) . ';' . PHP_EOL;
    }

    return $js;
  }

  private function toJson($any)
  {
    return json_encode($this->makeJsonEncodable($any));
  }

  private function makeJsonEncodable($any)
  {
    if (is_array($any) || is_object($any)) {
      $arr = [];

      foreach ($any as $i => $v) {
        $arr[$i] = $this->makeJsonEncodable($v);
      }

      $any = $arr;
    }

    return $any;
  }
}