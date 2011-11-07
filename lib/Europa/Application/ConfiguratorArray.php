<?php

namespace Europa\Application;

class ConfiguratorArray implements ConfiguratorInterface
{
    private $configs = array();
    
    public function add(ConfiguratorInterface $config)
    {
        $this->configs[] = $config;
        return $this;
    }
    
    public function configure(Container $container)
    {
        foreach ($this->configs as $config) {
            $config->configure($container);
        }
        return $container;
    }
}