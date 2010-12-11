<?php

abstract class AbstractController extends Europa_Controller
{
	public function init()
	{
		$view = str_replace('Controller', 'View', get_class($this));
		$view = str_replace('_', '/', $view);
		$this->setView(
			new Europa_View_Layout(
				new Europa_View_Php('DefaultLayout'),
				new Europa_View_Php($view)
			)
		);
	}
}