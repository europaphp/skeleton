<?php

namespace Europa\Controller\Filter;
use Europa\Exception\Exception;
use Europa\Controller\ControllerAbstract;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

/**
 * Ensure that type-hinted parameters are given a type.
 * 
 * @category   ControllerFilters
 * @package    Europa
 * @subpackage Controller
 * @author     Trey Shugart <treshugart@gmail.com>
 * @license    Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class TypeHint
{
    /**
     * Ensure that type-hinted parameters are given a type.
     * 
     * @param ControllerAbstract $controller The controller to filter.
     * @param ClassReflector     $class      The class reflector.
     * @param MethodReflector    $method     The method reflector.
     * 
     * @return void
     */
    public function __invoke(ControllerAbstract $controller, $class, $method)
    {
        $request = $controller->request();

        foreach ($method->getParameters() as $param) {
            if (!$type = $param->getClass()) {
                continue;
            }

            $type = $type->getName();
            $name = $param->getName();

            if ($request->hasParam($name)) {
                $value = $request->getParam($name);
            } elseif ($param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            } else {
                Exception::toss(
                    'Cannot type-hint "%s" in "%s::%s()" because the request does not contain the parameter and a default value was not specified.',
                    $name,
                    $class->getName(),
                    $method->getName()
                );
            }

            $request->setParam($name, new $type($value));
        }
    }
}