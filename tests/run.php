<?php

require_once '../lib/Europa/Loader.php';

Europa_Loader::registerAutoload();
Europa_Loader::addPath('../lib');
Europa_Loader::addPath('./');

$europa = new EuropaTest;
$europa->run();

?>

<?php foreach ($europa->getGroups() as $group): ?>
== <?php echo $group->getName(); ?> ==
Passed: <?php echo $group->countPassed(); ?>

Incomplete: <?php echo $group->countIncomplete(); ?>

Failed: <?php echo $group->countFailed(); ?>


<?php endforeach; ?>