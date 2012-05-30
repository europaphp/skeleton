<?php

namespace Europa\Di;

/**
 * Contains multiple configurations to apply to a single container.
 * 
 * @category Configurations
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ConfigurationArray implements ConfigurationInterface
{
    /**
     * List of configurations to use, in order of execution.
     * 
     * @var array
     */
    private $configs = array();
    
    /**
     * Adds a configuration to the array.
     * 
     * @param ConfigurationInterface $config The configuration to add.
     *  
     * @return ConfigurationArray
     */
    public function add(ConfigurationInterface $config)
    {
        $this->configs[] = $config;
        return $this;
    }
    
    /**
     * Configures the specified container.
     * 
     * @param \Europa\Di\Container $container The container to configure.
     * 
     * @return void
     */
    public function configure(Container $container)
    {
        foreach ($this->configs as $config) {
            $config->configure($container);
        }
        return $container;
    }
}