<?php

namespace Test;
use Europa\Unit\Test\Test;
use Europa\Request\Http;
use Provider\Controller\TestNamedParamController;

class Controller extends Test
{
    public function testNamedParamMapping()
    {
        $request    = new Http;
        $controller = new TestNamedParamController($request);
        
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