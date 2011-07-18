Since both the flash library and helper both implement IteratorAggregate, you can simply iterate over the object instead of explicitly having to call getIterator() on the object.
<?php foreach ($this->flash('error') as $item): ?>
<?php echo $item; ?>
<?php endforeach; ?>