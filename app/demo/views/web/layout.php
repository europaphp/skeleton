<!doctype html>
<html>
    <head>
        <?php echo $helpers->css->compile('css/lib/bootstrap'); ?>
        <?php echo $helpers->css->compile('css/lib/bootstrap-responsive'); ?>
        <title><?php echo $helpers->lang->title; ?></title>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1><?php echo $helpers->lang->heading ?> <small><?php echo $helpers->lang->tagline(round(microtime() - EUROPA_START_TIME, 3)) ?></small></h1>
            </div>
            <?php echo $this->renderChild(); ?>
    </body>
</html>