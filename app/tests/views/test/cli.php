<?php $this->extend('cli'); ?>

<?php if ($this->context('suite')->getAssertions()->isPassed()): ?>
<?php echo $this->helper('cli')->color('All tests passed!', 'green'); ?><?php else: ?>
<?php echo $this->helper('cli')->color('Failed', 'red'); ?>

<?php echo $this->helper('cli')->color('------', 'red'); ?>

<?php foreach ($this->context('suite')->getAssertions()->getFailed() as $ass): ?>
  <?php echo $this->helper('cli')->color($ass->getTestClass(), 'red/white'); ?>:<?php echo $this->helper('cli')->color($ass->getTestLine(), 'yellow'); ?> <?php echo $ass->getMessage(); ?>

<?php endforeach; ?>
<?php endif; ?>


<?php echo $this->helper('cli')->color('Coverage:', 'yellow'); ?> <?php echo $this->helper('cli')->color($this->context('percent') . '%', $this->context('percent') >= 50 ? 'green' : 'red'); ?>

<?php if ($this->context('suite')->getExceptions()->count()): ?>
Exceptions
----------

<?php foreach ($this->context('suite')->getExceptions() as $e): ?>
  <?php echo $this->helper('cli')->color($e->getFile(), 'red/white'); ?>:<?php echo $this->helper('cli')->color($e->getLine(), 'yellow'); ?> <?php echo $e->getMessage(); ?>

  <?php foreach ($e->getTrace() as $trace): ?>
  <?php echo (isset($trace['class']) ? $this->helper('cli')->color($trace['class'] . $trace['type'], 'cyan') : '') . $this->helper('cli')->color($trace['function'], 'cyan'); ?> <?php echo $this->helper('cli')->color('in', 'green'); ?> <?php echo isset($trace['file']) ? $this->helper('cli')->color(str_replace(realpath(__DIR__ . '/../../..'), '', $trace['file']) . ':' . $trace['line'], 'yellow') : $this->helper('cli')->color('unknown', 'red/white'); ?>

  <?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php if ($this->untested && $this->context('report')->getUntestedFileCount()): ?>
Untested Files and Lines
------------------------
<?php foreach ($this->context('report')->getUntestedFiles() as $file): ?>
<?php echo $this->helper('cli')->color($file, 'cyan'); ?>

<?php foreach ($file->getUntestedLines() as $line): ?>
  <?php echo $this->helper('cli')->color($line->getNumber(), 'yellow'); ?>: <?php echo $line; ?>
<?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>