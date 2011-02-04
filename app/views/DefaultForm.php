<form <?php echo $this->form->getAttributeString(); ?>>
    <?php echo new \Europa\View\Php('DefaultElementList', array('list' => $this->form)); ?>
    <?php echo new \Europa\Form\Element\Submit(array('value' => 'Log In')); ?>
</form>