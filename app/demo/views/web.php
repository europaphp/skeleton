<!doctype html>
<html>
    <head>
        <?php echo $this->css->compile('css/lib/bootstrap'); ?>
        <?php echo $this->css->compile('css/lib/bootstrap-responsive'); ?>
        <title>EuropaPHP</title>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>EuropaPHP <small>Rendered in <?php echo round(microtime() - EUROPA_START_TIME, 3) ?></small></h1>
            </div>
            <?php echo $this->renderChild(); ?>
    </body>
</html>