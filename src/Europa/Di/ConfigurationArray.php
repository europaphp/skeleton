<?php

namespace Europa\Di;
use Europa\Common\InstanceIterator;

class ConfigurationArray extends ConfigurationInterface
{
    private $configurations;

    public function __construct(Traversable $configurations)
    {
        $this->configurations = new InstanceIterator('Europa\Di\ConfigurationInterface', $configurations);
    }

    public function configure(ContainerInterface $container)
    {
        foreach ($this->configurations as $configuration) {
            $configuration->configure($container);
        }

        return $this;
    }
}