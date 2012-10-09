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
        $controller = new Controller(new Http);
        $controller->request()->setParams([
            'id'   => 1,
            'name' => 'Trey'
        ]);

        $controller->filter();
        $controller();
        
        $this->assert($controller->id === $controller->request()->id, 'Id does not match.');
        $this->assert($controller->name === $controller->request()->name, 'Name does not match.');
        $this->assert($controller->classFilter, 'The controller class was not filtered.');
        $this->assert($controller->methodFilter, 'The controller method was not filtered.');
    }

    public function badControllerActioning()
    {
        $controller = new BadController(new Http);
        $controller->filter();

        try {
            $controller();
            $this->assert(false, 'Exception should have been thrown because of bad filter call.');
        } catch (Exception $e) {}

        $controller->request()->setMethod('post');

        try {
            $controller();
            $this->assert(false, 'Exception should have been thrown because of undefined method "delete".');
        } catch (Exception $e) {}

        $controller->request()->setMethod('delete');

        try {
            $controller();
            $this->assert(false, 'Exception should have been thrown because of undefined method "delete".');
        } catch (Exception $e) {}
    }

    public function actioningAllMethod()
    {
        $controller = new AllController(new Http);
        $controller();
    }
}