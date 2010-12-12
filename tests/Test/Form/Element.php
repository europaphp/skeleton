<?php

class Test_Form_Element extends Testes_Test
{
	public function testFill()
	{
		$element = new Europa_Form_Element_Input;
		$element->name = 'my[test][element]';
		$element->fill(
			array(
				'my' => array(
					'test' => array(
						'element' => 'value'
					)
				)
			)
		);
		$this->assert(
		    $element->value === 'value',
		    'Filling does not work.'
		);
	}
	
	public function testToArraySimple()
	{
		$element        = new Europa_Form_Element_Input;
		$element->name  = 'my';
		$element->value = 'value';
		
		$toArray = $element->toArray();
		
		$valid = isset($toArray['my'])
		      && $toArray['my'] === 'value';
		
		$this->assert($valid, 'Simple to array failed.');
	}
	
	public function testToArrayStringNumericKey()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1]';
		$element->value = 'value';
		
		$toArray = $element->toArray();
		
		$valid = isset($toArray['my'])
		      && isset($toArray['my']['1'])
		      && $toArray['my']['1']=== 'value';
		
		$this->assert($valid, 'To array with a string key failed.');
	}
	
	public function testToArrayIntNumericKey()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1]';
		$element->value = 'value';
		
		$toArray = $element->toArray();
		
		$valid = isset($toArray['my'])
		      && isset($toArray['my'][1])
		      && $toArray['my'][1]=== 'value';
		
		$this->assert($valid, 'To array with a numeric key failed.');
	}
	
	public function testToArrayComplex()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1][test][0][element]';
		$element->value = 'value';
		
		$toArray = $element->toArray();
		
		$valid = isset($toArray['my'])
		      && isset($toArray['my']['1'])
		      && isset($toArray['my']['1']['test'])
		      && isset($toArray['my']['1']['test'][0])
		      && isset($toArray['my']['1']['test'][0]['element'])
		      && $toArray['my']['1']['test'][0]['element'] === 'value';
	    
	    $this->assert($valid, 'Complex to array failed.');
	}
}