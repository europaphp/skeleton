<?php

namespace Controller;
use Europa\Controller\Rest;
use Europa\Di\Container;

abstract class Base extends Rest
{
    public function init()
    {
        $view = Container::get()->view->get();
        $view->setScript(get_class($this));
        $this->setView($view);
    }
}