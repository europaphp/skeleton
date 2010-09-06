<!DOCTYPE html>
<html>
	<head>
		<?php echo $this->cssHelper(); ?>
		<title><?php echo $this->langHelper->title ?></title>
	</head>
	<body>
		<?php echo $this->view; ?>
		<div id="footer">
			<?php echo $this->langHelper->time(round(microtime() - EUROPA_START_TIME, 4)); ?>
			<?php echo $this->langHelper->memory(array('megabytes' => round(memory_get_peak_usage() / 1024 / 1024, 2))); ?>
		</div>
		<?php echo $this->js(); ?>
	</body>
</html>
