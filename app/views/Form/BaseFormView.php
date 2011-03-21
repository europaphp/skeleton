<form <?php echo $this->form->getAttributeString(); ?>>
    <?php echo new \Europa\View\Php('Form\BaseElementListView', array('list' => $this->form)); ?>
    <?php echo new \Europa\Form\Element\Submit(array('value' => 'Log In')); ?>
</form>