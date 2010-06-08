<?php

class Test_Loader extends Europa_Unit_Suite
{
	public function __construct()
	{
		$tests = array(
			'Test_Loader_AddPath',
			'Test_Loader_LoadClass',
			'Test_Loader_RegisterAutoload'
		);
		foreach ($tests as $test) {
			$this->add(new $test);
		}
	}
}