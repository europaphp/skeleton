<?php

namespace Europa\View;
use Europa\Di\ContainerAware;
use Europa\Di\ContainerAwareInterface;

class Php implements ContainerAwareInterface, ScriptAwareInterface, ViewInterface
{
    use ContainerAware, ScriptAware;

    private $child;

    private $extendStack = array();

    private $childScript;

    private $parentScript;

    private $context;

    public function render(array $context = [])
    {
        if (!$this->getScript()) {
            throw new Exception\UnspecifiedViewScript;
        }

        if (!$script = $this->locateScript()) {
            throw new Exception\InvalidViewScript($this->getScript());
        }

        // apply context
        $this->context = $context;

        // capture the output
        ob_start();

        // render
        include $script;

        // get output
        $rendered = ob_get_clean();

        // handle view extensions
        if ($this->parentScript) {
            // set the script so the parent has access to what child has been rendered
            $this->childScript = $this->getScript();

            // then set the parent script to the current script so the current instance is shared
            $this->setScript($this->parentScript);

            // reset the parent script to avoid recursion
            $this->parentScript = null;

            // set the rendered child so the parent has access to the rendered child
            $this->child = $rendered;

            // render and return the output of the parent
            return $this->render($context);
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
        $render = $this->render($context);

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
            throw new Exception\NoContainer($name, $this->getScript());
        }

        try {
            return $injector->get($name);
        } catch (\Exception $e) {
            throw new Exception\CannotGetHelper($name, $this->getScript(), $e->getMessage());
        }
    }

    public function extend($parent)
    {
        // the child is the current script
        $child = $this->getScript();

        // child views cannot extend themselves
        if ($parent === $child) {
            throw new Exception\CircularExtension;
        }

        // if the child has already extended a parent, don't do anything
        if (in_array($child, $this->extendStack)) {
            return $this;
        }

        // the extend stack makes sure that extend doesn't trigger recursion
        $this->extendStack[] = $child;

        // set the parent
        $this->parentScript = $parent;

        return $this;
    }
}