<?php

namespace Europa\App;

interface ModuleInterface
{
    public function bootstrap();

    public function getClassLocator();

    public function getLangLocator();

    public function getName();

    public function getRoutes();

    public function getViewLocator();
}