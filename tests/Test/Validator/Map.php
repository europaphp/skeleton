<?php

use Europa\Validator\Map;

/**
 * Tests for validating \Europa\Validator\Map
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Test_Validator_Map extends Testes_Test
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
     * @var \Europa\Validator\Map
     */
    private $validator;
    
    /**
     * Sets up the validator test.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->validator = Map::create()
            ->name->required()->addMessage(self::NAME_ERROR)
            ->age->number()->numberRange(18, 25)->addMessage(self::AGE_ERROR);
    }
    
    /**
     * Tests mapped data validation.
     * 
     * @return bool
     */
    public function testValidation()
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
            'The error that was raised should have been the date-of-birth error.'
        );
    }
}