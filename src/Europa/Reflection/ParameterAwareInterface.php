<?php

namespace Europa\Reflection;

interface ParameterAwareInterface
{
    public function mergeNamedArgs(array $params, $caseSensitive = false);
}