<?php

class Test_View_Layout extends Europa_Unit_Test
{
	public function setUp()
	{
		$this->_view = new Europa_View_Layout;
		$this->_view->setLayout(new Europa_View_Json(array('test1' => true)));
		$this->_view->setView(new Europa_View_Json(array('test2' => true)));
	}
	
	public function testParamSetting()
	{
		return $this->_view->getLayout()->test1
		    && $this->_view->getView()->test2;
	}
	
	public function testGlobalParamSetting()
	{
		$this->_view->globalParam = true;
		return $this->_view->getLayout()->globalParam === true
		    && $this->_view->getView()->globalParam   === true;
	}
	
	public function testGlobalParamUnsetting()
	{
		unset($this->_view->globalParam);
		return !isset($this->_view->getLayout()->globalParam)
		    && !isset($this->_view->getView()->globalParam);
	}
}