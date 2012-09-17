<?php $this->extend('cli/layout'); ?>

<?php if ($suite->getAssertions()->isPassed()): ?>
<?php echo $this->cli->color('All tests passed!', 'green'); ?><?php else: ?>
<?php echo $this->cli->color('Failed', 'red'); ?>

<?php echo $this->cli->color('------', 'red'); ?>

<?php foreach ($suite->getAssertions()->getFailed() as $ass): ?>
  <?php echo $this->cli->color($ass->getTestClass(), 'red/white'); ?>:<?php echo $this->cli->color($ass->getTestLine(), 'yellow'); ?> <?php echo $ass->getMessage(); ?>

<?php endforeach; ?>
<?php endif; ?>


<?php echo $this->cli->color('Coverage:', 'yellow'); ?> <?php echo $this->cli->color($percent . '%', $percent >= 50 ? 'green' : 'red'); ?>

<?php if ($showUntested && $report->getUntestedFileCount()): ?>

Untested Files and Lines
------------------------
<?php foreach ($report->getUntestedFiles() as $file): ?>
<?php echo $this->cli->color($file, 'red'); ?>

<?php foreach ($file->getUntestedLines() as $line): ?>
  <?php echo $this->cli->color($line->getNumber(), 'yellow'); ?>: <?php echo $line; ?>
<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>