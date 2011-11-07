<?php

namespace Controller;
use Europa\Application\Container;
use Europa\Controller\RestController;

abstract class Base extends RestController
{
    public function init()
    {
        $view = Container::get()->view->get();
        $view->setScript(get_class($this));
        $this->setView($view);
    }
}