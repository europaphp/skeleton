<?php

namespace Europa\View;
use Europa\Di\ContainerAware;
use Europa\Di\ContainerAwareInterface;

class Php implements ContainerAwareInterface, ScriptAwareInterface
{
  use ContainerAware, ScriptAware;

  private $child;

  private $extendStack = array();

  private $childScript;

  private $parentScript;

  private $context;

  public function __invoke(array $context = [])
  {
    if (!$this->getScript()) {
      throw new Exception\UnspecifiedViewScript;
    }

    if (!$script = $this->getLocatedScript()) {
      throw new Exception\InvalidViewScript(['view' => $this->getScript()]);
    }

    $this->context = $context;

    ob_start();
    include $script;
    $rendered = ob_get_clean();

    if ($this->parentScript) {
      $this->childScript = $this->getScript();
      $this->setScript($this->parentScript);
      $this->parentScript = null;
      $this->child = $rendered;

      return $this($context);
    }

    return $rendered;
  }

  public function renderScript($script, array $context = array())
  {
    // capture old state
    $oldScript = $this->getScript();
    $oldParent = $this->parentScript;
    $oldChild  = $this->childScript;

    // set new state
    $this->setScript($script);
    $this->parentScript = null;
    $this->childScript  = null;

    // capture rendered script
    $render = $this->__invoke($context);

    // reapply old state
    $this->setScript($oldScript);
    $this->parentScript = $oldParent;
    $this->childScript  = $oldChild;

    return $render;
  }

  public function renderChild()
  {
    return $this->child;
  }

  public function context($name = null)
  {
    if (!$name) {
      return $this->context;
    }

    return isset($this->context[$name]) ? $this->context[$name] : null;
  }

  public function helper($name)
  {
    if (!$injector = $this->getContainer()) {
      throw new Exception\NoContainer([
        'name' => $name,
        'view' => $this->getScript()
      ]);
    }

    try {
      return $injector($name);
    } catch (\Exception $e) {
      throw new Exception\CannotGetHelper([
        'name' => $name,
        'view' => $this->getScript(),
        'message' => $e->getMessage()
      ]);
    }
  }

  public function extend($parent)
  {
    $child = $this->getScript();

    if ($parent === $child) {
      throw new Exception\CircularExtension(['child' => $child]);
    }

    if (in_array($child, $this->extendStack)) {
      return $this;
    }

    $this->extendStack[] = $child;
    $this->parentScript = $parent;

    return $this;
  }
}