#!/usr/bin/php

<?php

// the base directory
$base = dirname(__FILE__) . DIRECTORY_SEPARATOR;

?>

<?php if ($argc === 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))): ?>
This is a command line script for building EuropaPHP. There should be an
xml file called "build.xml" in this directory.

  Usage: build.php component1 component2 ...
  
  NOTE: That by default, the docs component is always included. This
  includes files such as CHANGELOG, LICENSE and README.
<?php exit; endif; ?>

<?php if (!is_file($base . 'build.xml')): ?>
<?php die('Unable to locate the build file: build.xml'); ?>
<?php endif; ?>

<?php

// loader for easy loading
require $base . 'lib/pQuery.php';
require $base . 'lib/Europa/Build.php';
require $base . 'lib/Europa/Build/Exception.php';

// create a new build
$release = new Europa_Build(
	$base . 'build.xml'
	, $base
	. DIRECTORY_SEPARATOR
	. '..'
	. DIRECTORY_SEPARATOR
);

// the docs should be in every build
$release->addComponent('docs');

// add all passed components
foreach ($argv as $component) {
	$release->addComponent($component);
}

// output to browser
$release->save($base . 'EuropaPHP.zip');