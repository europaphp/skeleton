<?php

/**
 * A class used for application setup. Defined methods are called in
 * the order in which they are defined.
 * 
 * @category  Bootstrapper
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Bootstrapper
{
    /**
     * Goes through each method in the extending class and calls it
     * in them in the order in which they were defined.
     * 
     * @return void
     */
    public function init()
    {
        $class = new ReflectionClass($this);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name === __FUNCTION__) {
                continue;
            }
            $this->$name();
        }
    }
}