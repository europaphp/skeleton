<?php

namespace Europa\Di\Configuration;
use Europa\Di\Container;

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
