<?php

namespace Europa\Module;

interface ModuleInterface
{
    public function bootstrap(ManagerInterface $manager);
    public function config();
    public function name();
    public function path();
    public function version();
}