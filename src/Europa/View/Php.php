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
            throw new Exception\UnspecifiedViewScript('No view script was specified.');
        }

        if (!$script = $this->getLocatedScript()) {
            throw new Exception\InvalidViewScript(sprintf('The view script "%s" does not exist.', $this->getScript()));
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
            throw new Exception\NoContainer(sprintf(
                'Cannot get helper "%s" from view "%s" because no container was set.',
                $name,
                $this->getScript()
            ));
        }

        try {
            return $injector->get($name);
        } catch (\Exception $e) {
            throw new Exception\CannotGetHelper(sprintf(
                'Cannot get helper "%s" from view "%s" because: %s.',
                $name,
                $this->getScript(),
                $e->getMessage()
            ));
        }
    }

    public function extend($parent)
    {
        // the child is the current script
        $child = $this->getScript();

        // child views cannot extend themselves
        if ($parent === $child) {
            throw new Exception\CircularExtension('Child view cannot extend itself.');
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