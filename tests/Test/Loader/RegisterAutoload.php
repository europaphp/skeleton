<?php

class Test_Loader_RegisterAutoload extends Europa_Unit_Test
{
	public function run()
	{
		Europa_Loader::registerAutoload();
		$funcs = spl_autoload_functions();
		foreach ($funcs as $func) {
			if (
				is_array($func)
				&& $func[0] === 'Europa_Loader'
				&& $func[1] === 'loadClass'
			) {
				return true;
			}
		}
		return false;
	}
}