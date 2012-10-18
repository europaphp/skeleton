<?php $this->extend('cli'); ?>

<?php if ($this->context('command')): ?>
<?php echo $this->context('description') . PHP_EOL; ?>

Usage
-----

<?php echo $this->helper('cli')->color('bin/cli', 'yellow'); ?> <?php echo $this->helper('cli')->color($this->context('command'), 'green'); ?> <?php echo $this->helper('cli')->color('[options]', 'yellow'); ?>


<?php if ($this->context('params')): ?>
Options
-------
<?php foreach ($this->context('params') as $name => $param): ?>
  <?php echo $this->helper('cli')->color(str_replace('$', '--', $name), 'green') . str_repeat(' ', $param['pad']); ?> <?php echo $param['description']; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php else: ?>
Usage
-----

<?php echo $this->helper('cli')->color('php bin/cli [command] [options]' . PHP_EOL, 'yellow'); ?>

To see the available commands:
  <?php echo $this->helper('cli')->color('php bin/cli help --command [command]' . PHP_EOL, 'yellow'); ?>

To see this help message again:
  <?php echo $this->helper('cli')->color('php bin/cli' . PHP_EOL, 'yellow'); ?>
  <?php echo $this->helper('cli')->color('php bin/cli help' . PHP_EOL, 'yellow'); ?>

<?php echo $this->helper('cli')->color('Available Commands' . PHP_EOL, 'cyan'); ?>
<?php echo $this->helper('cli')->color('------------------' . PHP_EOL, 'cyan'); ?>

<?php foreach ($this->context('commands') as $command => $description): ?>
<?php echo $this->helper('cli')->color($command, 'green'); ?> <?php echo $description; ?>

<?php endforeach; ?>

<?php echo $this->helper('cli')->color('*', 'cyan'); ?> To document commands, just update the doc blocks of the <?php echo $this->helper('cli')->color('cli', 'cyan'); ?> action you want to document.
<?php echo $this->helper('cli')->color('*', 'cyan'); ?> To author your own command, simply create a controller and give it a <?php echo $this->helper('cli')->color('cli', 'cyan'); ?> action.

<?php endif; ?>