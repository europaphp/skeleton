<?php

namespace Test\All\View;
use Europa\View\Php;
use Testes\Test\UnitAbstract;

class PhpTest extends UnitAbstract
{
    function rendering()
    {
        $view = new Php;
        $view->setScriptLocator(function($script) {
            return __DIR__ . '/../../Provider/View/' . $script . '.phtml';
        });

        $view->setScript('test');

        $this->assert($view() === 'test', 'The view was not correctly rendered.');
    }

    function helpers()
    {
        $view = new Php;
        $view->setServiceContainer(function($name) {
            $class = 'Test\Provider\View\\' . ucfirst($name);
            return new $class;
        });

        $this->assert($view->helper('helper')->test() === 'test', 'The helper was not called.');
    }
}