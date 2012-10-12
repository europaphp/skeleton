<!doctype html>
<html>
    <head>
        <?php echo $this->css->compile('css/lib/bootstrap'); ?>
        <?php echo $this->css->compile('css/lib/bootstrap-responsive'); ?>
        <title><?php echo $this->lang->title; ?></title>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1><?php echo $this->lang->heading ?> <small><?php echo $this->lang->tagline(round(microtime() - EUROPA_START_TIME, 3)) ?></small></h1>
            </div>
            <?php echo $this->renderChild(); ?>
    </body>
</html>