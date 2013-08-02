<?php

namespace Europa\Module;

interface ModuleInterface
{
  public function bootstrap(callable $container);

  public function ns();

  public function name();

  public function version();

  public function path();

  public function config();

  public function dependencies();
}