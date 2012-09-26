<?php

namespace Test\All\Controller;
use Europa\Request;
use Europa\Response;
use Exception;
use Test\Provider\Controller\TestNamedParamController;
use Testes\Test\UnitAbstract;

class RestControllerTest extends UnitAbstract
{
    public function testNamedParamMapping()
    {
        $request    = new Request\Http;
        $response   = new Response\Http;
        $controller = new TestNamedParamController($request, $response);
        
        $request->id   = 1;
        $request->name = 'Trey';
        
        try {
            $controller->action();
        } catch (Exception $e) {
            $this->assert(false, 'An error occurred while actioning the controller.');
        }
        
        $this->assert(
            $controller->id   === $request->id,
            $controller->name === $request->name
        );
    }
}