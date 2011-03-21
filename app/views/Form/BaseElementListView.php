<dl>
    <?php foreach ($this->list as $element): ?>
    <dt><label for="<?php echo $element->id; ?>"><?php echo $element->label; ?></label></dt>
    <dd>
        <?php if ($element instanceof \Europa\Form\ElementList): ?>
        <?php echo new static($this->getScript(), array('list' => $element)); ?>
        <?php else: ?>
        <?php echo $element; ?>
        <?php endif; ?>
    </dd>
    <?php endforeach; ?>
</dl>