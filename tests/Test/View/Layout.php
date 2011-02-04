<?php

class Test_View_Layout extends Testes_Test
{
    public function setUp()
    {
        $this->_view = new \Europa\View\Layout;
        $this->_view->setLayout(new \Europa\View\Json(array('test1' => true)));
        $this->_view->setView(new \Europa\View\Json(array('test2' => true)));
    }
    
    public function testParamSetting()
    {
        $valid = $this->_view->getLayout()->test1
            && $this->_view->getView()->test2;
        
        $this->assert($valid, 'Parameter setting not working.');
    }
    
    public function testGlobalParamSetting()
    {
        $this->_view->globalParam = true;
        
        $valid = $this->_view->getLayout()->globalParam === true
            && $this->_view->getView()->globalParam   === true;
        
        $this->assert($valid, 'Global parameter setting not working.');
    }
    
    public function testGlobalParamUnsetting()
    {
        unset($this->_view->globalParam);
        $valid = !isset($this->_view->getLayout()->globalParam)
            && !isset($this->_view->getView()->globalParam);
        
        $this->assert($valid, 'Global parameter unsetting not working.');
    }
}