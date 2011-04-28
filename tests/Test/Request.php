<?php

namespace Test;
use Europa\Request\Http;
use Europa\Unit\Test\Test;

class Request extends Test
{
    private $request;
    
    public function setUp()
    {
        $this->request = new Http;
        $this->request->setParams(array('cascade1' => false, 'cascade2' => true))
            ->setParams(array('cascade1' => true, 'controller' => 'customcontroller'));
    }
    
    public function testHttpParamCascading()
    {
        $valid = $this->request->cascade1 === true
            && $this->request->cascade2 === true;
        $this->assert($valid, 'Parameter cascading not working.');
    }
    
    public function testHttpParamSetting()
    {
        $this->request->testSet1 = true;
        $this->assert(
            $this->request->testSet1 == true,
            'Parameter setting not working.'
        );
    }
    
    public function testHttpParamBulk()
    {
        $this->request->setParams(array('bulk1' => true));
        $this->assert(
            $this->request->bulk1 === true,
            'Bulk setting not working.'
        );
    }
    
    public function testHttpControllerSetting()
    {
        $this->assert(
            $this->request->getController() === 'customcontroller',
            'Controller setting not working.'
        );
    }
    
    public function testHttpParamClearing()
    {
        $this->request->removeParams();
        $this->assert(
            $this->request->bulk2 === null,
            'Parameter removing not working.'
        );
    }
}