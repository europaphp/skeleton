<?php

namespace Europa\App;
use Europa\Filter;
use Europa\Reflection;
use Europa\View;

class ViewFilter
{
  private $ccsplit;

  public function __construct()
  {
    $this->ccsplit = new Filter\CamelCaseSplitFilter;
  }

  public function __invoke(callable $renderer, callable $controller)
  {
    if ($renderer instanceof View\ScriptAwareInterface) {
      $this->applyScriptFromController($renderer, $controller);
    }
  }

  private function applyScriptFromController(View\ScriptAwareInterface $renderer, callable $controller)
  {
    $controller = new Reflection\CallableReflector($controller);

    if ($controller->getReflector() instanceof Reflection\MethodReflector) {
      $renderer->setScript($this->formatScript($controller->getReflector()));
    }
  }

  private function formatScript(Reflection\MethodReflector $method)
  {
    return $this->formatClass($method->getClass()) . '/' . $this->formatMethod($method);
  }

  private function formatClass(Reflection\ClassReflector $class)
  {
    $class = $class->getName();
    $class = explode('\\', $class);

    foreach ($class as &$part) {
      $part = $this->ccsplit->__invoke($part);
      $part = array_map('strtolower', $part);
      $part = implode('-', $part);
    }

    return implode('/', $class);
  }

  private function formatMethod(Reflection\MethodReflector $method)
  {
    $method = $method->getName();
    $method = $this->ccsplit->__invoke($method);

    return implode('-', $method);
  }
}