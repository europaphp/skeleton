<?php

class Test_Loader_AddPath extends Europa_Unit_Test
{
	public function run()
	{
		Europa_Loader::addPath('.');
		return true;
	}
}