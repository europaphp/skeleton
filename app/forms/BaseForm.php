<?php

class BaseForm extends \Europa\Form
{
    public function __toString()
    {
        return (string) new \Europa\View\Php('DefaultForm', array('form' => $this));
    }
}