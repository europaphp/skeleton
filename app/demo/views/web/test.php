<?php $this->extend('web/layout'); ?>

<h3>
    <?php echo $helpers->lang->coverage ?>:
    <?php if ($context->percent >= 60): ?>
    <span class="text-success"><?php echo $context->percent ?>%</span>
    <?php else: ?>
    <span class="text-error"><?php echo $context->percent ?>%</span>
    <?php endif ?>
</h3>

<?php if ($context->suite->getAssertions()->getFailed()->count()): ?>
<h3><?php echo $helpers->lang->failedTests ?></h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th><?php echo $helpers->lang->class ?></th>
            <th><?php echo $helpers->lang->line ?></th>
            <th><?php echo $helpers->lang->message ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($context->suite->getAssertions()->getFailed() as $ass): ?>
        <tr>
            <td><?php echo $ass->getTestClass() ?></td>
            <td><?php echo $ass->getTestLine() ?></td>
            <td><?php echo $ass->getMessage() ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<?php if ($context->suite->getExceptions()->count()): ?>
<h3><?php echo $helpers->lang->exceptions ?></h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th><?php echo $helpers->lang->file ?></th>
            <th><?php echo $helpers->lang->line ?></th>
            <th><?php echo $helpers->lang->message ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($context->suite->getExceptions() as $ex): ?>
        <tr>
            <td><?php echo $ex->getFile() ?></td>
            <td><?php echo $ex->getLine() ?></td>
            <td><?php echo $ex->getMessage() ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<?php if ($context->untested && $context->report->getUntestedFileCount()): ?>

<h3><?php echo $helpers->lang->untested ?></h3>

<?php foreach ($context->report->getUntestedFiles() as $file): ?>
<h4><?php echo $file ?></h4>

<table class="table">
    <tbody>
        <?php foreach ($file->getUntestedLines() as $line): ?>
        <tr>
            <td><?php echo $line->getNumber() ?></td>
            <td><code><?php echo $line ?></code></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach ?>
<?php endif ?>