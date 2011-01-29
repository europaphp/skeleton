<?php

class Test_Controller extends Testes_Test
{
    public function testNamedParamMapping()
    {
        $request    = new \Europa\Request\Http;
        $controller = new Test_Controller_TestNamedParamController($request);
        
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

class Test_Controller_TestNamedParamController extends \Europa\Controller
{
    public $id;
    
    public $name;
    
    public $notRequired;
    
    public function get($id, $name, $notRequired = true)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->notRequired = $notRequired;
    }
}