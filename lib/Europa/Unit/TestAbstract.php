<?php

namespace Europa\Unit;

/**
 * Basic class for any type of test (benchmark, unit test).
 * 
 * @category Testing
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart
 */
abstract class TestAbstract implements Runable
{
    /**
     * Returns all public methods that are valid test methods.
     * 
     * @return array
     */
    public function getMethods()
    {
        $exclude = array();
        $include = array();
        $self    = new \ReflectionClass($this);

        // find each method to exclude
        foreach ($self->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }
        
        // exclude methods
        foreach ($self->getMethods() as $method) {
        	// only public methods
        	if (!$method->isPublic()) {
        		continue;
        	}
            
            // make sure it was delcared by the test class
            if ($method->getDeclaringClass()->getName() !== get_class($this)) {
                continue;
            }

        	// we only need the method name now
            $method = $method->getName();

            // only if it isn't in the exlusion list
            if (in_array($method, $exclude)) {
                continue;
            }

            // add it to the inclusion list
            $include[] = $method;
        }
        
        // return the inclusion list
        return array_unique($include);
    }
}