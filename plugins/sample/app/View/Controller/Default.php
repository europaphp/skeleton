<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->css->render($this->getScript()); ?>
        <?php echo $this->js->render($this->getScript()); ?>
        <title><?php echo $this->lang->title ?></title>
    </head>
    <body>
        <div id="body"><?php echo $this->getRenderedChild(); ?></div>
        <div id="footer">
            <p><?php echo $this->lang->time(round(microtime(true) - START_TIME, 3)); ?></p>
        </div>
    </body>
</html>
