<!doctype html>
<html>
    <head>
        <?php echo $this->helper('css')->compile('css/lib/bootstrap'); ?>
        <?php echo $this->helper('css')->compile('css/lib/bootstrap-responsive'); ?>
        <title>EuropaPHP</title>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>EuropaPHP</h1>
            </div>
            <?php echo $this->renderChild(); ?>
    </body>
</html>