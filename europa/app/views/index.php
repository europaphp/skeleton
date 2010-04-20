<!DOCTYPE html>
<html>
	<head>
		<title>EuropaPHP</title>
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
		<h1><?php echo $this->title; ?></h1>
		<?php echo $this->view; ?>
		<div id="footer">
			Rendered in <?php echo round(microtime() - EUROPA_START_TIME, 4); ?> seconds
			using <?php echo round(memory_get_peak_usage() / 1024 / 1024, 2); ?> MegaBytes of memory.
		</div>
	</body>
</html>
