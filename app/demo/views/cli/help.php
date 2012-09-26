<?php $this->extend('cli/layout'); ?>

<?php if (isset($context->command)): ?>
<?php echo $context->description . PHP_EOL; ?>

Usage:
  <?php echo $helpers->cli->color('bin/cli', 'yellow'); ?> <?php echo $helpers->cli->color($context->command, 'green'); ?> <?php echo $helpers->cli->color('[options]', 'yellow'); ?>


<?php if ($context->params): ?>
<?php echo $helpers->lang->options; ?>

<?php foreach ($context->params as $name => $param): ?>
  <?php echo $helpers->cli->color(str_replace('$', '--', $name), 'green') . str_repeat(' ', $param['pad']); ?> <?php echo $param['description']; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php else: ?>
<?php echo $helpers->lang->usage; ?>

  <?php echo $helpers->cli->color('php bin/cli [command] [options]' . PHP_EOL, 'yellow'); ?>

<?php echo $helpers->lang->seeHelp; ?>

  <?php echo $helpers->cli->color('php bin/cli help --command [command]' . PHP_EOL, 'yellow'); ?>

<?php echo $helpers->lang->seeAgain; ?>

  <?php echo $helpers->cli->color('php bin/cli' . PHP_EOL, 'yellow'); ?>
  <?php echo $helpers->cli->color('php bin/cli help' . PHP_EOL, 'yellow'); ?>

<?php echo $helpers->cli->color($helpers->lang->availableCommands . PHP_EOL, 'cyan'); ?>
<?php echo $helpers->cli->color($helpers->lang->availableCommandsUnderline . PHP_EOL, 'cyan'); ?>

<?php foreach ($context->commands as $command => $description): ?>
<?php echo $helpers->cli->color($command, 'green'); ?> <?php echo $description; ?>

<?php endforeach; ?>

<?php echo $helpers->cli->color('*', 'cyan'); ?> <?php echo $helpers->lang->howToDocument; ?>

<?php echo $helpers->cli->color('*', 'cyan'); ?> <?php echo $helpers->lang->howToAuthor; ?>

<?php endif; ?>