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

// shift off the script name
array_shift($argv);

?>

building...<?php

require_once dirname(__FILE__) . '/../lib/Europa/Loader.php';
Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../lib');

// create a new build
$schema  = new Package_Schema(new pQuery($base . 'build.xml'));
$release = new Package_Builder($schema, $base . '../');

// add all passed components
foreach ($argv as $package) {
	$release->add($package);
}
exit;

// output to browser
$release->save($base . 'EuropaPHP.zip');

?>done!

Saved as EuropaPHP.zip in build directory.

