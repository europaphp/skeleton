<?php

namespace Europa\Form\Element;

/**
 * A default form select element.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Select extends ElementAbstract
{
    /**
     * The value of the selected option.
     * 
     * @var mixed
     */
    private $value = '';
    
    /**
     * The options to apply to the select.
     * 
     * @var array
     */
    private $options = array();
    
    /**
     * Constructs and sets defaults.
     * 
     * @return \Europa\Form\Element\Select
     */
    public function __construct(array $attributes = array())
    {
        if (isset($attributes['value'])) {
            $this->value = $attributes['value'];
            unset($attributes['value']);
        }
        
        if (isset($attributes['options'])) {
            $this->options = $attributes['options'];
            unset($attributes['options']);
        }
        
        parent::__construct($attributes);
    }
    
    /**
     * Renders the reset element.
     * 
     * @return string
     */
    public function __toString()
    {
        $attr = $this->getAttributeString();
        $html = '<select'
              . ($attr ? ' ' . $attr : '')
              . '>';
        
        foreach ($this->options as $label => $value) {
            $selected = '';
            if ($this->value == $value) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
        }
        
        $html .= '</select>';
        return $html;
    }
}
