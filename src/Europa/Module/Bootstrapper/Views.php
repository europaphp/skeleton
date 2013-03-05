<?php

namespace Europa\Module\Bootstrapper;
use Europa\Fs\Locator;

trait Views
{
    public function views()
    {
        if ($path = realpath($this->module->path() . '/views')) {
            $locator = new Locator;
            $locator->addPath($path);
            $this->injector->get('viewLocators')->append($locator);
        }
    }
}