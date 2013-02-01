<?php

namespace Test\All\Reflection;
use Europa\Reflection\MethodReflector;
use Exception;
use Testes\Test\UnitAbstract;

class MethodReflectorTest extends UnitAbstract
{
    public function testParameterMapping()
    {
        $method = new MethodReflector('Test\Provider\Reflection\Mapping', 'someMethod');
        
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