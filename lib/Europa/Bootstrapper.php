<?php

/**
 * A class used for application setup. Defined methods are called in
 * the order in which they are defined.
 * 
 * @category Bootstrapper
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Europa_Bootstrapper
{
    /**
     * Goes through each method in the extending class and calls them
     * in the order in which they were defined.
     * 
     * @return void
     */
    final public function boot()
    {
        $class = new ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name === __FUNCTION__ || strpos($name, '__') === 0 || (isset($this->$name) && !$this->$name)) {
                continue;
            }
            $this->$name();
        }
    }
}