<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $this->langHelper->title ?></title>
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
			#body {
				margin: 6px auto;
				width: 350px;
			}
			#footer {
				text-align: center;
				font-style: italic;
				font-size: 0.8em;
			}
		</style>
	</head>
	<body>
		<?php echo $this->getChild(); ?>
		<div id="footer">
			<?php echo $this->langHelper->time(round(microtime() - EUROPA_START_TIME, 4)); ?>
			<?php echo $this->langHelper->memory(array('megabytes' => round(memory_get_peak_usage() / 1024 / 1024, 2))); ?>
		</div>
	</body>
</html>
