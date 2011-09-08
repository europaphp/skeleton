<?php

namespace Test\Reflection;
use Europa\Reflection\MethodReflector as MethodReflectorObject;
use Testes\Test;

class MethodReflector extends Test
{
    public function testParameterMapping()
    {
        $method = new MethodReflectorObject('\Provider\Reflection\Mapping', 'someMethod');
        
        try {
            $result = $method->mergeNamedArgs(
                array(
                    'id'   => 1,
                    'name' => 'Name'
                )
            );
        } catch (\Exception $e) {
            $this->assert(false, "The parameters couldn't be merged.");
        }
        
        $this->assert(
            $result[0]    === 1
            && $result[1] === 'Name'
            && $result[2] === true,
            'The parameters were not merged properly'
        );
    }
}