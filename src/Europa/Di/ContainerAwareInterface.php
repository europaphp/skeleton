<?php

namespace Europa\Di;

interface ContainerAwareInterface
{
  public function getContainer();

  public function setContainer(callable $container);
}