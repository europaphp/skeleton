<?php

namespace Test\Validator;
use Europa\Validator\Map as MapObject;
use Testes\Test;

/**
 * Tests for validating \Europa\Validator\Map
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Map extends Test
{
    /**
     * The name error message.
     * 
     * @var string
     */
    const NAME_ERROR = 'Please enter your name.';
    
    /**
     * The name error message.
     * 
     * @var string
     */
    const AGE_ERROR = 'You must be between 18 and 25.';
    
    /**
     * The validator map doing the validation.
     * 
     * @var MapObject
     */
    private $validator;
    
    /**
     * Sets up the validator test.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->validator = MapObject::create()
            ->name->required()->string()->addMessage(self::NAME_ERROR)
            ->age->number()->numberRange(18, 25)->addMessage(self::AGE_ERROR);
    }
    
    /**
     * Tests mapped data validation.
     * 
     * @return bool
     */
    public function testForcingAgeError()
    {
        $data = array(
            'name' => 'Trey Shugart',
            'age'  => 28
        );
        
        $this->assert(
            !$this->validator->validate($data)->isValid(),
            'Validation failing.'
        );
        
        $this->assert(
            count($this->validator->getMessages()) === 1,
            'Only 1 validation error should have been raised.'
        );
        
        $this->assert(
            end($this->validator->getMessages()) === self::AGE_ERROR,
            'The error that was raised should have been the age error.'
        );
    }
    
    public function testForcingNameError()
    {
        $data = array(
            'name' => '',
            'age'  => 25
        );
        
        $this->assert(
            !$this->validator->validate($data)->isValid(),
            'Validation failing.'
        );
        
        $this->assert(
            count($this->validator->getMessages()) === 1,
            'Only 1 validation error should have been raised.'
        );
        
        $this->assert(
            end($this->validator->getMessages()) === self::NAME_ERROR,
            'The error that was raised should have been the name error.'
        );
    }
}