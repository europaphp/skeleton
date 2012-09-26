<?php $this->extend('cli/layout'); ?>

<?php if ($context->suite->getAssertions()->isPassed()): ?>
<?php echo $helpers->cli->color('All tests passed!', 'green'); ?><?php else: ?>
<?php echo $helpers->cli->color('Failed', 'red'); ?>

<?php echo $helpers->cli->color('------', 'red'); ?>

<?php foreach ($context->suite->getAssertions()->getFailed() as $ass): ?>
  <?php echo $helpers->cli->color($ass->getTestClass(), 'red/white'); ?>:<?php echo $helpers->cli->color($ass->getTestLine(), 'yellow'); ?> <?php echo $ass->getMessage(); ?>

<?php endforeach; ?>
<?php endif; ?>


<?php echo $helpers->cli->color('Coverage:', 'yellow'); ?> <?php echo $helpers->cli->color($context->percent . '%', $context->percent >= 50 ? 'green' : 'red'); ?>

<?php if ($context->showUntested && $context->report->getUntestedFileCount()): ?>

Untested Files and Lines
------------------------
<?php foreach ($context->report->getUntestedFiles() as $file): ?>
<?php echo $helpers->cli->color($file, 'cyan'); ?>

<?php foreach ($file->getUntestedLines() as $line): ?>
  <?php echo $helpers->cli->color($line->getNumber(), 'yellow'); ?>: <?php echo $line; ?>
<?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>