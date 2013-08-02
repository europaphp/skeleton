<?php

namespace Europa\View;
use Europa\Config;

class Json
{
  private $config = [
    'json_encode_options' => 0
  ];

  public function __construct($config = [])
  {
    $this->config = new Config\Config($this->config, $config);
  }

  public function __invoke(array $context = array())
  {
    $render = $this->formatParamsToJsonArray($context);
    $render = json_encode($context, $this->config['options']);
    return $render;
  }

  private function formatParamsToJsonArray($data)
  {
    $array = array();

    foreach ($data as $name => $item) {
      if (is_array($item) || is_object($item)) {
        $item = $this->formatParamsToJsonArray($item);
      }

      $array[$name] = $item;
    }

    return $array;
  }
}