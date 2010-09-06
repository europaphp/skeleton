<?php

abstract class AbstractController extends Europa_Controller_Standard
{
	public function __construct(Europa_Request $request)
	{
		parent::__construct($request);
		$viewScript = Europa_String::create($request->getController());
		$viewScript->replace('_', '/')->toClass();
		$this->setView(
			new Europa_View_Layout(
				new Europa_View_Php('DefaultLayout'),
				new Europa_View_Php($viewScript . 'View')
			)
		);
	}
}