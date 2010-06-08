<?php

class Test_All extends Europa_Unit_Suite
{
	public function __construct()
	{
		$this->add(new Test_Loader)
		     ->add(new Test_Request);
	}
}