<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->css; ?>
        <?php echo $this->js; ?>
        <title><?php echo $this->lang->title ?></title>
    </head>
    <body>
        <div id="body"><?php echo $this->getChild('view'); ?></div>
        <div id="footer">
            <?php echo $this->lang->time(\Europa\Registry::get('bench')->getTime(3)); ?>
            <?php echo $this->lang->memory(array('megabytes' => \Europa\Registry::get('bench')->getMemory(2))); ?>
        </div>
    </body>
</html>
