<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->css('css/lib/layout'); ?>
        <title><?php echo $this->lang->title; ?></title>
    </head>
    <body>
        <div id="body"><?php echo $this->renderChild(); ?></div>
        <div id="footer">
            <p><?php echo $this->lang->time(round(microtime() - EUROPA_START_TIME, 3)); ?></p>
        </div>
    </body>
</html>
