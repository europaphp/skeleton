<?php

class Test_Reflection_MethodReflector extends Testes_Test
{
    public function testParameterMapping()
    {
        $method = new \Europa\Reflection\MethodReflector('Test_Reflection_MappingProvider', 'someMethod');
        
        try {
            $result = $method->mergeNamedArgs(
                array(
                    'id'   => 1,
                    'name' => 'Name'
                )
            );
        } catch (Exception $e) {
            $this->assert(false, 'The parameters couldn\'t be merged.');
        }
        
        $this->assert(
            $result[0]    === 1
            && $result[1] === 'Name'
            && $result[2] === true,
            'The parameters were not merged properly'
        );
    }
}

class Test_Reflection_MappingProvider extends Test_Reflection_MappingProviderAbstract
{
    
}

abstract class Test_Reflection_MappingProviderAbstract
{
    public function someMethod($id, $name, $notRequired = true)
    {
        
    }
}