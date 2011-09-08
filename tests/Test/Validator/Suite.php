<?php

namespace Test\Validator;
use Europa\Validator\Suite as SuiteObject;
use Testes\Test;

/**
 * Tests for validating \Europa\Validator\Suite
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Suite extends Test
{
    /**
     * Tests to make sure it fails if all sub-tests fail.
     * 
     * @return bool
     */
    public function testFailAllValidators()
    {
        $suite = SuiteObject::required()->number();
        $this->assert(
            $suite->validate(null)->isValid() === false,
            'Could not fail all validators.'
        );
    }
    
    /**
     * Tests to make sure it passes if all tests pass.
     * 
     * @return bool
     */
    public function testPassAllValidators()
    {
        $suite = SuiteObject::required()->number();
        $this->assert(
            $suite->validate('1')->isValid() === true,
            'Could not pass all validators.'
        );
    }
    
    /**
     * Tests to make sure it fails if one or more sub-tests fail.
     * 
     * @return bool
     */
    public function testPassOneValidator()
    {
        $suite = SuiteObject::required()->number();
        $this->assert(
            $suite->validate('something')->isValid() === false,
            'Could not pass one validator.'
        );
    }
}