<?php

namespace Provider\Controller;
use Europa\Controller;

class TestNamedParamController extends Controller
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