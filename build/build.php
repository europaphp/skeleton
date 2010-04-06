#!/usr/bin/php
<?php

// the base directory
$base = dirname(__FILE__) . DIRECTORY_SEPARATOR;

if ($argc === 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))):

?>

This is a command line script for building EuropaPHP. There should be an xml
file called "build.xml" in this directory. Dependencies are automatically
built in depending on the components chosen.

  Usage: build.php component1 component2 ...

<?php

	exit;
endif;

if (!is_file($base . 'build.xml')):
	die('Unable to locate the build file: build.xml');
endif;

?>

building...<?php

// loader for easy loading
require $base . 'lib/pQuery.php';
require $base . 'lib/Europa/Build.php';
require $base . 'lib/Europa/Build/Exception.php';

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
foreach ($argv as $component) {
	$release->addComponent($component);
}

// output to browser
$release->save($base . 'EuropaPHP.zip');

?>done!

Saved as EuropaPHP.zip in build directory.

