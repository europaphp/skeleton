<?php echo $this->test->countPassed(); ?> passed
<?php if ($this->verbose): ?>
<?php foreach ($this->test->getPassed() as $test): ?>
  - <?php echo $test->getName(); ?>

<?php endforeach; ?>
<?php endif; ?>
<?php echo $this->test->countIncomplete(); ?> incomplete
<?php if ($this->verbose): ?>
<?php foreach ($this->test->getIncomplete() as $test): ?>
  - <?php echo $test->getName(); ?>

<?php endforeach; ?>
<?php endif; ?>
<?php echo $this->test->countFailed(); ?> failed
<?php if ($this->verbose): ?>
<?php foreach ($this->test->getFailed() as $test): ?>
  - <?php echo $test->getName(); ?>

<?php endforeach; ?>
<?php endif; ?>