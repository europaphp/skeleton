<?php

namespace Test\All\Controller;
use Europa\Request\Http;
use Exception;
use Test\Provider\Controller\AllController;
use Test\Provider\Controller\BadController;
use Test\Provider\Controller\Controller;
use Testes\Test\UnitAbstract;

class ControllerTest extends UnitAbstract
{
    public function actioning()
    {
        $controller = new Controller;
        $request    = new Http;

        $request->setParams([
            'action' => 'test',
            'id'     => 1,
            'name'   => 'Trey'
        ]);

        $controller($request);
        
        $this->assert($controller->id === $request->id, 'Id does not match.');
        $this->assert($controller->name === $request->name, 'Name does not match.');
        $this->assert($controller->classFilter, 'The controller class was not filtered.');
        $this->assert($controller->methodFilter, 'The controller method was not filtered.');
    }

    public function badControllerActioning()
    {
        $controller = new BadController;
        $request    = new Http;

        try {
            $controller($request);
            $this->assert(false, 'Exception should have been thrown because of bad filter call.');
        } catch (Exception $e) {}

        try {
            $controller($request->setParam('action', 'post'));
            $this->assert(false, 'Exception should have been thrown because of undefined method "delete".');
        } catch (Exception $e) {}

        try {
            $controller($request->setParam('action', 'delete'));
            $this->assert(false, 'Exception should have been thrown because of undefined method "delete".');
        } catch (Exception $e) {}
    }
}