<?php

namespace Europa\Config\Adapter\To;
use Europa\Config\Config;

class Ini
{
  private $config = [
    'sections' => false
  ];

  public function __construct($config = [])
  {
    $this->config = new Config($this->config, $config);
  }

  public function __invoke($data)
  {
    if ($this->config['sections']) {
      $content = '';

      foreach ($data as $name => $value) {
        $content .= '[' . $name . "]\n";
        $content .= $this->makeIniString($value);
      }
    } else {
      $content = $this->makeIniString($data);
    }

    return $content;
  }

  private function makeIniString($data, $prefix = null)
  {
    $content = '';

    foreach ($data as $name => $value) {
      $fullname = $prefix . $name;

      if (is_array($value) || is_object($value)) {
        $content .= $this->makeIniString($value, $fullname . '.');
      } else {
        $content .= $fullname . ' = "' . $value ."\"\n";
      }
    }

    return $content;
  }
}