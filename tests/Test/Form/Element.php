<?php

class Test_Form_Element extends Europa_Unit_Test
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
		return $element->value === 'value';
	}
	
	public function testToArraySimple()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my';
		$element->value = 'value';
		$toArray = $element->toArray();
		return isset($toArray['my'])
		    && $toArray['my'] === 'value';
	}
	
	public function testToArrayStringNumericKey()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1]';
		$element->value = 'value';
		$toArray = $element->toArray();
		return isset($toArray['my'])
		    && isset($toArray['my']['1'])
		    && $toArray['my']['1']=== 'value';
	}
	
	public function testToArrayIntNumericKey()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1]';
		$element->value = 'value';
		$toArray = $element->toArray();
		return isset($toArray['my'])
		    && isset($toArray['my'][1])
		    && $toArray['my'][1]=== 'value';
	}
	
	public function testToArrayComplex()
	{
		$element = new Europa_Form_Element_Input;
		$element->name  = 'my[1][test][0][element]';
		$element->value = 'value';
		$toArray = $element->toArray();
		return isset($toArray['my'])
		    && isset($toArray['my']['1'])
		    && isset($toArray['my']['1']['test'])
		    && isset($toArray['my']['1']['test'][0])
		    && isset($toArray['my']['1']['test'][0]['element'])
		    && $toArray['my']['1']['test'][0]['element'] === 'value';
	}
}