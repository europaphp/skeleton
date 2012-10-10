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

<?php if ($context->suite->getExceptions()->count()): ?>
Exceptions
----------

<?php foreach ($context->suite->getExceptions() as $e): ?>
  <?php echo $helpers->cli->color($e->getFile(), 'red/white'); ?>:<?php echo $helpers->cli->color($e->getLine(), 'yellow'); ?> <?php echo $e->getMessage(); ?>

  <?php foreach ($e->getTrace() as $trace): ?>
  <?php echo (isset($trace['class']) ? $helpers->cli->color($trace['class'] . $trace['type'], 'cyan') : '') . $helpers->cli->color($trace['function'], 'cyan'); ?> <?php echo $helpers->cli->color('in', 'green'); ?> <?php echo isset($trace['file']) ? $helpers->cli->color(str_replace(realpath(__DIR__ . '/../../..'), '', $trace['file']) . ':' . $trace['line'], 'yellow') : $helpers->cli->color('unknown', 'red/white'); ?>

  <?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php if ($context->untested && $context->report->getUntestedFileCount()): ?>
Untested Files and Lines
------------------------
<?php foreach ($context->report->getUntestedFiles() as $file): ?>
<?php echo $helpers->cli->color($file, 'cyan'); ?>

<?php foreach ($file->getUntestedLines() as $line): ?>
  <?php echo $helpers->cli->color($line->getNumber(), 'yellow'); ?>: <?php echo $line; ?>
<?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>