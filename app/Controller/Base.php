<?php

namespace Controller;
use Europa\Controller\RestController;
use Europa\Di\Container;

abstract class Base extends RestController
{
    public function init()
    {
        $view = Container::get()->view->get();
        $view->setScript(get_class($this));
        $this->setView($view);
    }
}