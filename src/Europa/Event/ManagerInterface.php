<?php

namespace Europa\Event;

interface ManagerInterface
{
    public function bind($name, callable $callback);

    public function unbind($name, callable $callback = null);

    public function trigger($name);

    public function triggerArray($name, array $args = []);
}