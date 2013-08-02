<?php

namespace Europa\Di;
use Europa\Iterator\CallableIterator;
use Traversable;

class ConfigurationArray
{
  private $configurations;

  public function __construct(Traversable $configurations)
  {
    $this->configurations = new CallableIterator($configurations);
  }

  public function __invoke(ContainerInterface $container)
  {
    foreach ($this->configurations as $configuration) {
      $configuration->configure($container);
    }

    return $this;
  }
}