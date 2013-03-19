<?php

namespace Europa\Reflection;

interface ReflectorInterface
{
    public function __toString();

    public function getDocBlock();
}