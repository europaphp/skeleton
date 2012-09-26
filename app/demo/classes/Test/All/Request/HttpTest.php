<?php

namespace Test\All\Request;
use Europa\Request\Http;
use Testes\Test\UnitAbstract;

class HttpTest extends UnitAbstract
{
    private $request;
    
    public function setUp()
    {
        $this->request = new Http;
        $this->request
            ->setParams(array('cascade1' => false, 'cascade2' => true))
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
    
    public function testHttpParamClearing()
    {
        $this->request->removeParams();
        $this->assert(
            $this->request->bulk2 === null,
            'Parameter removing not working.'
        );
    }

    public function testHttpParamGettingUsingRegex()
    {
        $this->request->param1     = true;
        $this->request->param2     = true;
        $this->request->param3     = true;
        $this->request->testParam1 = true;
        $this->request->testParam2 = true;
        $this->request->testParam3 = true;

        $all = $this->request->searchParams('.*');
        $this->assert(count($all) === 6, 'The total nubmer of parameters returned is incorrect.');

        $firstThree = $this->request->searchParams('^param');
        $this->assert(count($firstThree) === 3, 'First set of parameters expected.');

        $lastThree = $this->request->searchParams('^test');
        $this->assert(count($lastThree) === 3, 'Last set of parameters expected.');

        $onlyTwos = $this->request->searchParams('(2)$');
        $this->assert(count($onlyTwos) === 2, 'Only the parameters ending in two should have been returned.');
    }
}