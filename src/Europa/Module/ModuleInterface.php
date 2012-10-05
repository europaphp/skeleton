<?php

namespace Europa\Module;

interface ModuleInterface
{
    public function name();

    public function bootstrap();
}