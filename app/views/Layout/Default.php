<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->uri->format('css/' . $this->getScript() . '.css'); ?>">
        <title><?php echo $this->lang->title; ?></title>
    </head>
    <body>
        <div id="body"><?php echo $this->renderChild(); ?></div>
        <div id="footer">
            <p><?php echo $this->lang->time(round(microtime(true) - EUROPA_START_TIME, 3)); ?></p>
        </div>
    </body>
</html>
