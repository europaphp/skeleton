<?php

/**
 * A default form select element.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Form_Element_Select extends Europa_Form_Element
{
    /**
     * The value of the selected option.
     * 
     * @var mixed
     */
    private $_value = '';
    
    /**
     * The options to apply to the select.
     * 
     * @var array
     */
    private $_options = array();
    
    /**
     * Constructs and sets defaults.
     * 
     * @return Europa_Form_Element_Select
     */
    public function __construct(array $attributes = array())
    {
        if (isset($attributes['value'])) {
            $this->_value = $attributes['value'];
            unset($attributes['value']);
        }
        
        if (isset($attributes['options'])) {
            $this->_options = $attributes['options'];
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
        foreach ($this->_options as $label => $value) {
            $selected = '';
            if ($this->_value == $value) {
                $selected = ' selected="selected"';
            }
            $html .= '<option value="' . $value . '"' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}