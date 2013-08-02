<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Filter\ToStringFilter;

class Xml
{
  private $config = [
    'declare' => true,
    'encoding' => 'UTF-8',
    'indent' => true,
    'numeric-key-name' => 'item',
    'root-node' => 'xml',
    'spaces' => 2,
    'version' => '1.0'
  ];

  private $toStringFilter;

  public function __construct($config = [])
  {
    $this->config     = new Config($this->config, $config);
    $this->toStringFilter = new ToStringFilter;
  }

  public function __invoke(array $context = [])
  {
    $str = '';

    if ($this->config['declare']) {
      $str = '<?xml version="'
        . $this->toStringFilter->__invoke($this->config['version'])
        . '" encoding="'
        . $this->config['encoding']
        . '" ?>'
        . PHP_EOL;
    }

    // Render a root node if given a name.
    if ($this->config['root-node']) {
      $context = [$this->config['root-node'] => $context];
    }

    // Render the XML tree.
    foreach ($context as $name => $content) {
      $str .= $this->renderNode($name, $content);
    }

    // Remove whitespace before returning.
    return trim($str);
  }

  private function renderNode($name, $content, $level = 0)
  {
    $keys = $this->config['numeric-key-name'];

    // translate a numeric key to a replacement key
    if (is_numeric($name)) {
      if (is_string($keys)) {
        $name = $keys;
      } elseif (is_array($keys) && isset($keys[$level])) {
        $name = $keys[$level];
      }
    }

    // indent the node
    $ind = $this->indent($level);
    $str = $ind . "<{$name}>";

    // render child nodes if the value is traversable
    if (is_array($content) || is_object($content)) {
      $str .= PHP_EOL;

      foreach ($content as $k => $v) {
        $str .= $this->renderNode($k, $v, $level + 1);
      }

      $str .= $ind;
    } else {
      $str .= $this->toStringFilter->__invoke($content);
    }

    $str .= "</{$name}>";
    $str .= PHP_EOL;

    return $str;
  }

  private function indent($level)
  {
    $indent = $this->config['spaces'];
    $indent = $indent ? str_repeat(' ', $indent) : "\t";
    return str_repeat($indent, $level);
  }
}