<?php

/**
 * Tests for validating \Europa\Validator\Suite
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Validator_Suite extends Testes_Test
{
    /**
     * Tests to make sure it fails if all sub-tests fail.
     * 
     * @return bool
     */
    public function testFailAllValidators()
    {
        $suite   = new \Europa\Validator\Suite;
        $suite[] = new \Europa\Validator\Required;
        $suite[] = new \Europa\Validator\Number;
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
        $suite   = new \Europa\Validator\Suite;
        $suite[] = new \Europa\Validator\Required;
        $suite[] = new \Europa\Validator\Number;
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
        $suite   = new \Europa\Validator\Suite;
        $suite[] = new \Europa\Validator\Required;
        $suite[] = new \Europa\Validator\Number;
        $this->assert(
            $suite->validate('something')->isValid() === false,
            'Could not pass one validator.'
        );
    }
}