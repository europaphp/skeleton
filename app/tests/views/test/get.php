<?php $this->extend('web'); ?>

<h3>
    Coverage:
    <?php if ($this->context('percent') >= 60): ?>
    <span class="text-success"><?php echo $this->context('percent') ?>%</span>
    <?php else: ?>
    <span class="text-error"><?php echo $this->context('percent') ?>%</span>
    <?php endif ?>
</h3>

<?php if ($this->context('suite')->getAssertions()->getFailed()->count()): ?>
<h3>Failed Tests:</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Class</th>
            <th>Line</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->context('suite')->getAssertions()->getFailed() as $ass): ?>
        <tr>
            <td><?php echo $ass->getTestClass() ?></td>
            <td><?php echo $ass->getTestLine() ?></td>
            <td><?php echo $ass->getMessage() ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<?php if ($this->context('suite')->getExceptions()->count()): ?>
<h3>Exceptions</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>File</th>
            <th>Line</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->context('suite')->getExceptions() as $ex): ?>
        <tr>
            <td><?php echo $ex->getFile() ?></td>
            <td><?php echo $ex->getLine() ?></td>
            <td><?php echo $ex->getMessage() ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>

<?php if ($this->context('untested') && $this->context('report')->getUntestedFileCount()): ?>

<h3>Untested</h3>

<?php foreach ($this->context('report')->getUntestedFiles() as $file): ?>
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