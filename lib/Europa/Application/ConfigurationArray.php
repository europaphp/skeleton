<?php

namespace Europa\Application;

class ConfigurationArray implements ConfigurationInterface
{
    private $configs = array();
    
    public function add(ConfigurationInterface $config)
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