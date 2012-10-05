<?php

namespace Europa\Module;
use IteratorAggregate;

interface ManagerInterface extends IteratorAggregate
{
    public function bootstrap();
}