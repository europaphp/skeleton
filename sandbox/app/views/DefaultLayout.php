<!DOCTYPE html>
<html>
	<head>
		<?php echo $this->css; ?>
		<title><?php echo $this->lang->title ?></title>
	</head>
	<body>
		<?php echo $this->view; ?>
		<div id="footer">
			<?php echo $this->lang->time(round(microtime() - EUROPA_START_TIME, 4)); ?>
			<?php echo $this->lang->memory(array('megabytes' => round(memory_get_peak_usage() / 1024 / 1024, 2))); ?>
		</div>
		<?php echo $this->js; ?>
	</body>
</html>
