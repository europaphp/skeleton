<?php

namespace Europa\Di;

interface ConfigurationArrayInterface extends ConfigurationInterface
{
    private $configurations = [];

    public function configure(ContainerInterface $container)
    {
        foreach ($this->configurations as $configuration) {
            $configuration->configure($container);
        }

        return $this;
    }

    public function add(ConfigurationInterface $configuration)
    {
        $this->configurations[] = $configuration;
        return $this;
    }
}