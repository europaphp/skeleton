<?php

namespace Europa\Event;

interface EmitterInterface
{
    public function on($name, callable $callback);

    public function off($name, callable $callback = null);

    public function emit($name);

    public function emitArray($name, array $args = []);
}