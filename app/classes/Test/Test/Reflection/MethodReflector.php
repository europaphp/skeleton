<?php

namespace Test\Test\Reflection;
use Europa\Reflection;
use Exception;
use Testes\Test\Test;

class MethodReflector extends Test
{
    public function testParameterMapping()
    {
        $method = new Reflection\MethodReflector('\Provider\Reflection\Mapping', 'someMethod');
        
        try {
            $result = $method->mergeNamedArgs(
                array(
                    'id'   => 1,
                    'name' => 'Name'
                )
            );
        } catch (Exception $e) {
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