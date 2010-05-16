<?php

class ErrorController extends AbstractController
{
	public function __call($name, $args)
	{
		$this->notFoundAction();
		$this->_view->setScript('error/notFound');
	}
	
	public function notFoundAction()
	{
		
	}
}