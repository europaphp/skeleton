<!doctype html>
<html>
    <head>
        <?php echo $helpers->css('css/lib/layout'); ?>
        <title><?php echo $helpers->lang->title; ?></title>
    </head>
    <body>
        <div id="body"><?php echo $this->renderChild(); ?></div>
        <div id="footer">
            <p><?php echo $helpers->lang->time(round(microtime() - EUROPA_START_TIME, 3)); ?></p>
        </div>
    </body>
</html>
