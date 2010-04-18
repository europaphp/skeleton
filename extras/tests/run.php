<?php

/** 
 * @author Trey Shugart
 * 
 * CLI script for running specified test controllers. Output is formatted
 * to YAML for easy readability as well as parsing.
 */

require_once dirname(__FILE__) . '/../lib/Europa/Loader.php';

Europa_Loader::registerAutoload();
Europa_Loader::addPath(dirname(__FILE__) . '/../lib');
Europa_Loader::addPath(dirname(__FILE__));

// take off the first element
array_shift($argv);

if ($argc < 2) {
	die("\nYou must pass which test controllers to run.\n");
}

?>
<?php foreach ($argv as $testController): ?>	
<?php

$europa = new $testController;
$europa->run();

?>
<?php foreach ($europa->getGroups() as $group): ?>
<?php echo $group->getName(); ?>

  Passed: <?php echo $group->countPassed(); ?>

  Incomplete: <?php echo $group->countIncomplete(); ?>

  Failed: <?php echo $group->countFailed(); ?>

  Total: <?php echo $group->countTotal(); ?>

<?php endforeach; ?>
<?php endforeach;