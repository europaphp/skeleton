<?php

use Europa\String;
use Europa\View;

/**
* Contains general HTML form elements that can be automated for the view.
* 
* @category Helpers
* @package  Europa
* @author   Trey Shugart <treshugart@gmail.com>
* @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FormHelper
{
    /**
     * The default namespace to use.
     * 
     * @var string
     */
    const NS = 'Europa\Form\Element';
    
    /**
     * The view that called the helper.
     * 
     * @var \Europa\View
     */
    private $view;
    
    /**
     * The form element namespace to use.
     * 
     * @var string
     */
    private $namespace;
    
    /**
     * Constructs a new form helper.
     * 
     * @param \Europa\View $view The view that called the form helper.
     * 
     * @return FormHelper
     */
    public function __construct(View $view, $namespace = self::NS)
    {
        $this->view      = $view;
        $this->namespace = $namespace;
    }
    
    /**
     * Generates a form element from the name of the method and the array of attributes
     * passed in.
     * 
     * @param string $name       The type of element to generate.
     * @param array  $attributes The first element should be empty or an array of attributes.
     * 
     * @return \Europa\Form\Element
     */
    public function __call($name, array $attributes = array())
    {
        return $this->generateElement($name, isset($attributes[0]) ? $attributes[0] : array());
    }
    
    /**
     * Generates an input element while automating some parameters.
     * 
     * @param string $name       The name of the element to generate.
     * @param array  $attributes The attributes of the element to generate.
     * 
     * @return \Europa\Form\Element
     */
    private function generateElement($name, array $attributes = array())
    {
        if (!isset($attributes['id']) && isset($attributes['name'])) {
            $attributes['id'] = String::create($attributes['name'])->slug('-')->__toString();
        }
        
        if (!isset($attributes['value']) && isset($attributes['name'])) {
            $attributes['value'] = $this->view->param->__get($attributes['name']);
        }
        
        if (isset($attributes['title'])) {
            $attributes['title'] = $this->view->lang->__get($attributes['title']);
        } elseif (isset($attributes['name'])) {
            $attributes['title'] = $this->view->lang->__get($attributes['name']);
        }
        
        if (isset($attributes['label'])) {
            $attributes['label'] = $this->view->lang->__get($attributes['label']);
        } elseif (isset($attributes['title'])) {
            $attributes['label'] = $this->view->lang->__get($attributes['title']);
        }
        
        $class = '\\' . $this->namespace . '\\' . ucfirst($name);
        return new $class($attributes);
    }
}