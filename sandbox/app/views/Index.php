<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $this->lang->title ?></title>
		<style type="text/css">
			body {
				font-size: 1em;
				font-family: Georgia, Helvetica, Verdana, Arial;
				color: #333;
				margin-top: 200px;
			}
			h1 {
				text-align: center;
				font-size: 1.4em;
				font-style: italic;
				margin-top: 0px;
				margin-bottom: 6px;
			}
			h2 {
				font-size: 1em;
				margin: 6px;
				text-align: center;
			}
			#footer {
				text-align: center;
				font-style: italic;
				font-size: 0.8em;
			}
		</style>
	</head>
	<body>
		<?php echo Europa_Request_Http::getActiveInstance()->getView()->toString(); ?>
		<div id="footer">
			<?php echo $this->lang->time(round(microtime() - EUROPA_START_TIME, 4)); ?>
			<?php echo $this->lang->memory(array('megabytes' => round(memory_get_peak_usage() / 1024 / 1024, 2))); ?>
		</div>
	</body>
</html>
