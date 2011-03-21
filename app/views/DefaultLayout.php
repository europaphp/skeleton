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
            <p>Nope, no token "rendered in" benchmark here.</p>
        </div>
    </body>
</html>
