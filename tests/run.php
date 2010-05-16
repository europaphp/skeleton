<?php

/** 
 * @author Trey Shugart
 * 
 * CLI script for running specified test controllers. Output is formatted
 * to YAML for easy readability as well as parsing.
 */

require_once dirname(__FILE__) . '/../europa/lib/Europa/Loader.php';

Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../europa/lib');
Europa_Loader::addPath(dirname(__FILE__));

// take off the first element
array_shift($argv);

if ($argc < 2) {
	$tests = array('AllTests');
} else {
	array_shift($argv);
	$tests = $argv;
}

echo "Running Unit Tests:\n";
foreach ($tests as $group) {
	$group = new $group;
	$group->run();
	echo "{$group->countPassed()} passed\n";
	foreach ($group->getPassed() as $test) {
		echo " - {$test->getName()}\n";
	}
	echo "{$group->countIncomplete()} incomplete\n";
	foreach ($group->getIncomplete() as $test) {
		echo " - {$test->getName()}\n";
	}
	echo "{$group->countFailed()} failed\n";
	foreach ($group->getFailed() as $test) {
		echo " - {$test->getName()}\n";
	}
}