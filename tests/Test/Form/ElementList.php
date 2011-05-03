<?php

namespace Test\Form;
use Europa\Unit\Test\Test;
use Europa\Validator\Rule\Required;
use Provider\Form\TestElementList;

/**
 * Tests for validating \Europa\Form\ElementList.
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class ElementList extends Test
{
    /**
     * The list that is being used for the test.
     * 
     * @var \Europa\Form\ElementList
     */
    private $list;
    
    /**
     * Set up the test.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->list = new TestElementList;
        
        $this->list['user[0][name]']           = new \Europa\Form\Element\Input;
        $this->list['user[0][bio]']            = new \Europa\Form\Element\Textarea;
        $this->list['user[0][name]']->value    = 'tres';
        $this->list['user[0][bio]']->value     = 'php dev';
        $this->list['user[zero][name]']        = new \Europa\Form\Element\Input;
        $this->list['user[zero][bio]']         = new \Europa\Form\Element\Textarea;
        $this->list['user[zero][name]']->value = 'tres';
        $this->list['user[zero][bio]']->value  = 'php dev';
    }
    
    /**
     * Tests list output.
     * 
     * @return bool
     */
    public function testElementExistence()
    {
        $this->assert(
            $this->list['user[0][name]'] instanceof \Europa\Form\Element\Input,
            'Element does not exist.'
        );
    }
    
    /**
     * Tests validation on all elements in the list.
     * 
     * @return bool
     */
    public function testValidation()
    {
        $required = new Required;
        $required->addMessage('Name is required.');
        
        $this->assert(
            $this->list->validate()->isValid(),
            'Element is not valid.'
        );
    }
    
    /**
     * Tests the toArray value when using numeric indicies.
     * 
     * @return bool
     */
    public function testNumericToArray()
    {
        $toArray = $this->list->toArray();
        
        $valid = isset($toArray['user'][0]['name'])
            && isset($toArray['user'][1]['bio'])
            && $toArray['user'][0]['name'] === 'tres'
            && $toArray['user'][1]['bio']  === 'php dev';
        
        $this->assert($valid, 'Numeric indices in toArray failed.');
    }
    
    /**
     * Tests the toArray value when using string indicies that aren't numeric.
     * 
     * @return bool
     */
    public function testStringToArray()
    {
        $toArray = $this->list->toArray();
        
        $valid = isset($toArray['user']['zero']['name'])
              && isset($toArray['user']['zero']['bio'])
              && $toArray['user']['zero']['name'] === 'tres'
              && $toArray['user']['zero']['bio']  === 'php dev';
        
        $this->assert($valid, 'String indicies in toArray failed.');
    }
}