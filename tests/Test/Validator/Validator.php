<?php

/**
 * Tests for validating Europa\RouteValidator.
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Validator_Validator extends Testes_Test
{
    /**
     * Tests the required validator.
     * 
     * @return bool
     */
    public function testRequired()
    {
        $required = new \Europa\Validator\Rule\Required;
        $valid    = $required->validate(true)->isValid()
            && $required->validate('something')->isValid()
            && !$required->validate(null)->isValid()
            && !$required->validate(false)->isValid()
            && !$required->validate('')->isValid()
            && !$required->validate(array())->isValid();
        
        $this->assert($valid, 'Required validator not working.');
    }
    
    /**
     * Tests the number validator.
     * 
     * @return bool
     */
    public function testNumber()
    {
        $number = new \Europa\Validator\Rule\Number;
        $valid  = $number->validate('0')->isValid()
            && $number->validate(0)->isValid()
            && !$number->validate(null)->isValid()
            && !$number->validate(false)->isValid()
            && !$number->validate('something')->isValid()
            && !$number->validate(array())->isValid()
            && !$number->validate(true)->isValid();
        
        $this->assert($valid, 'Number validator not working.');
    }
    
    /**
     * Tests the number range validator.
     * 
     * @return bool
     */
    public function testNumberRange()
    {
        $range = new \Europa\Validator\Rule\NumberRange(1, 10);
        $valid = $range->validate(1)->isValid()
            && $range->validate(10)->isValid();
        
        $this->assert($valid, 'Number range validator not working.');
    }
    
    /**
     * Tests the alpha character validator.
     * 
     * @return bool
     */
    public function testAlpha()
    {
        $alpha = new \Europa\Validator\Rule\Alpha;
        $valid = $alpha->validate('something')->isValid()
            && !$alpha->validate('s0m3th1ng')->isValid();
        
        $this->assert($valid, 'Alpha validator not working.');
    }
    
    /**
     * Tests the alpha-numeric character validator.
     * 
     * @return bool
     */
    public function testAlphaNumeric()
    {
        $alnum = new \Europa\Validator\Rule\AlphaNumeric;
        $valid = $alnum->validate('s0m3th1ng')->isValid()
            && $alnum->validate('000000000')->isValid()
            && $alnum->validate('something')->isValid()
            && !$alnum->validate('s o m e t h i n g')->isValid()
            && !$alnum->validate('s-o-m-e-t_h_i_n_g')->isValid();
        
        $this->assert($valid, 'Alpha-numeric validator not working.');
    }
}