<?php

class Test_Request_Params_Http extends Testes_Test
{    
    protected $_request;
    
    public function setUp()
    {
        $this->_request = new \Europa\Request\Http;
        $this->_request->setParams(array('cascade1' => false, 'cascade2' => true))
                       ->setParams(array('cascade1' => true, 'controller' => 'customcontroller'));
    }
    
    public function testHttpParamCascading()
    {
        $valid = $this->_request->cascade1 === true
              && $this->_request->cascade2 === true;
        
        $this->assert($valid, 'Parameter cascading not working.');
    }
    
    public function testHttpParamSetting()
    {
        $this->_request->testSet1 = true;
        
        $this->assert(
            $this->_request->testSet1 == true,
            'Parameter setting not working.'
        );
    }
    
    public function testHttpParamBulk()
    {
        $this->_request->setParams(array('bulk1' => true));
        
        $this->assert(
            $this->_request->bulk1 === true,
            'Bulk setting not working.'
        );
    }
    
    public function testHttpControllerSetting()
    {
        $this->assert(
            $this->_request->getController() === 'customcontroller',
            'Controller setting not working.'
        );
    }
    
    public function testHttpParamClearing()
    {
        $this->_request->removeParams();
        
        $this->assert(
            $this->_request->bulk2 === null,
            'Parameter removing not working.'
        );
    }
}