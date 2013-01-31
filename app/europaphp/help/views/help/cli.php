<?php $this->extend('cli'); ?>

<?php if ($this->context('command')): ?>
<?php echo $this->context('description') . PHP_EOL; ?>

Usage
-----

<?php

echo $this->helper('cli')->color('bin/cli', 'yellow');
echo ' ';
echo $this->helper('cli')->color($this->context('command'), 'green');

if ($this->context('params')) {
    echo $this->helper('cli')->color(' [options]', 'cyan');
}

if ($this->context('params')):

?>


Options
-------
<?php

foreach ($this->context('params') as $name => $param) {
    echo '  ' . $this->helper('cli')->color(str_replace('$', '--', $name), 'green');
    echo ' ' . $param['description'];
    echo PHP_EOL;
}

endif;
else:

?>

Usage
-----

<?php echo $this->helper('cli')->color('php www/index.php [command] [options]' . PHP_EOL, 'yellow'); ?>

To see the documentation for a specific command, run:
  <?php echo $this->helper('cli')->color('php www/index.php help --command [command]' . PHP_EOL, 'yellow'); ?>

<?php echo $this->helper('cli')->color('Available Commands' . PHP_EOL, 'cyan'); ?>
<?php echo $this->helper('cli')->color('------------------' . PHP_EOL, 'cyan'); ?>

<?php

foreach ($this->context('commands') as $command => $description) {
    echo $this->helper('cli')->color($command, 'green'); ?> <?php echo $description;
    echo PHP_EOL;
}

?>

<?php echo $this->helper('cli')->color('*', 'cyan'); ?> To document commands, just update the doc blocks of the <?php echo $this->helper('cli')->color('cli', 'cyan'); ?> action you want to document.
<?php echo $this->helper('cli')->color('*', 'cyan'); ?> To author your own command, simply create a controller and give it a <?php echo $this->helper('cli')->color('cli', 'cyan'); ?> action.

<?php endif; ?>