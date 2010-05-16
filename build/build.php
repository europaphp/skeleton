#!/usr/bin/php
<?php

// the base directory
$base = dirname(__FILE__) . DIRECTORY_SEPARATOR;

if ($argc === 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))):

?>

This is a command line script for building EuropaPHP. There should be an xml
file called "build.xml" in this directory. Dependencies are automatically
built in depending on the package chosen.

  Usage: build.php package1 package2 ...

<?php

	exit;
endif;

if (!is_file($base . 'build.xml')):
	die('Unable to locate the build file: build.xml');
endif;

?>

building...<?php

require_once dirname(__FILE__) . '/../europa/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/lib');
Europa_Loader::addPath(dirname(__FILE__) . '/../vendor');
Europa_Loader::addPath(dirname(__FILE__) . '/../europa');

// create a new build
$release = new Europa_Build(
	$base
	. 'build.xml',
	$base
	. DIRECTORY_SEPARATOR
	. '..'
	. DIRECTORY_SEPARATOR
);

// add all passed components
foreach ($argv as $package) {
	$release->addPackage($package);
}

// output to browser
$release->save($base . 'EuropaPHP.zip');

?>done!

Saved as EuropaPHP.zip in build directory.

