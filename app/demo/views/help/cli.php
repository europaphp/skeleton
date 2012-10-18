<?php $this->extend('cli'); ?>

<?php if (isset($context->command)): ?>
<?php echo $context->description . PHP_EOL; ?>

Usage:
  <?php echo $this->cli->color('bin/cli', 'yellow'); ?> <?php echo $this->cli->color($context->command, 'green'); ?> <?php echo $this->cli->color('[options]', 'yellow'); ?>


<?php if ($context->params): ?>
<?php echo $this->lang->options; ?>

<?php foreach ($context->params as $name => $param): ?>
  <?php echo $this->cli->color(str_replace('$', '--', $name), 'green') . str_repeat(' ', $param['pad']); ?> <?php echo $param['description']; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php else: ?>
<?php echo $this->lang->usage; ?>

  <?php echo $this->cli->color('php bin/cli [command] [options]' . PHP_EOL, 'yellow'); ?>

<?php echo $this->lang->seeHelp; ?>

  <?php echo $this->cli->color('php bin/cli help --command [command]' . PHP_EOL, 'yellow'); ?>

<?php echo $this->lang->seeAgain; ?>

  <?php echo $this->cli->color('php bin/cli' . PHP_EOL, 'yellow'); ?>
  <?php echo $this->cli->color('php bin/cli help' . PHP_EOL, 'yellow'); ?>

<?php echo $this->cli->color($this->lang->availableCommands . PHP_EOL, 'cyan'); ?>
<?php echo $this->cli->color($this->lang->availableCommandsUnderline . PHP_EOL, 'cyan'); ?>

<?php foreach ($context->commands as $command => $description): ?>
<?php echo $this->cli->color($command, 'green'); ?> <?php echo $description; ?>

<?php endforeach; ?>

<?php echo $this->cli->color('*', 'cyan'); ?> <?php echo $this->lang->howToDocument; ?>

<?php echo $this->cli->color('*', 'cyan'); ?> <?php echo $this->lang->howToAuthor; ?>

<?php endif; ?>