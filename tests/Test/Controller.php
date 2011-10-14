<?php

namespace Test;
use Europa\Request\Http as Request;
use Europa\Response\Http as Response;
use Provider\Controller\TestNamedParamController;
use Testes\Test;

class Controller extends Test
{
    public function testNamedParamMapping()
    {
        $request    = new Request;
        $response   = new Response;
        $controller = new TestNamedParamController($request, $response);
        
        $request->id   = 1;
        $request->name = 'Trey';
        
        try {
            $controller->action();
        } catch (\Exception $e) {
            $this->assert(false, 'An error occurred while actioning the controller.');
        }
        
        $this->assert(
            $controller->id   === $request->id,
            $controller->name === $request->name
        );
    }
}