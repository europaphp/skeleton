<?php

class Test_Request extends Europa_Unit_Suite
{
	public function __construct()
	{
		$tests = array(
			'Dispatch',
			'Params'
		);
		
		// test base on test environment for requests
		$prefix = 'Test_Request_';
		if (Europa_Request::isCli()) {
			$prefix .= 'Cli';
		} else {
			$prefix .= 'Http';
			$tests[] = 'RequestUriDetection';
			$tests[] = 'RootUriDetection';
		}
		
		// modify prefix
		foreach ($tests as $k => $test) {
			$test = $prefix . '_' . $test;
			$this->add(new $test);
		}
	}
}