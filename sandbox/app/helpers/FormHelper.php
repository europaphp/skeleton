<?php

class FormHelper
{
	protected $_request;
	
	public function __construct(Europa_View $view)
	{
		$this->_request = new Europa_Request_Http;
		$this->_request->controller = 'form';
	}
	
	public function form($form, array $params = array())
	{
		$this->_request->action = $form;
		$this->_request->getLayout()->setParams($params);
		$this->_request->getView()->setParams($params);
		return $this;
	}
	
	public function __toString()
	{
		return $this->_request->__toString();
	}
}