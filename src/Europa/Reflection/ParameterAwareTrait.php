<?php

namespace Europa\Reflection;

trait ParameterAwareTrait
{
    public function mergeNamedArgs(array $params, $caseSensitive = false)
    {
        $merged = [];

        foreach ($params as $name => $value) {
            if (is_numeric($name)) {
                $merged[(int) $name] = $value;
            } elseif (!$caseSensitive) {
                $params[strtolower($name)] = $value;
            }
        }

        foreach ($this->getParameters() as $param) {
            $pos  = $param->getPosition();
            $name = $caseSensitive ? $param->getName() : strtolower($param->getName());

            if (array_key_exists($name, $params)) {
                $merged[$pos] = $params[$name];
            } elseif (array_key_exists($pos, $params)) {
                $merged[$pos] = $params[$pos];
            } elseif ($param->isOptional()) {
                $merged[$pos] = $param->getDefaultValue();
            } else {
                throw new Exception\InvalidParameter([
                    'name' => $param->getName(),
                    'function' => $this->__toString()
                ]);
            }
        }

        return $merged;
    }
}