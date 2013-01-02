<?php

namespace Europa\Controller\Filter;
use Europa\Exception\Exception;
use Europa\Controller\ControllerAbstract;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

class TypeHint
{
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