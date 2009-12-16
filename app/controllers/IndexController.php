<?php

class IndexController
{
	public function indexAction()
	{
		$this->layout->title = 'EuropaPHP';
		$this->view->hello   = 'Hello World';
	}
}