<?php

namespace Europa\Config;

/**
 * A trait that allows an configuration to be applied to it.
 * 
 * @category Config
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
trait Configurable
{
    /**
     * Config object.
     * 
     * @var array
     */
    private $config;

    /**
     * Sets or returns an config object. If returning a config object and one does not already exist, then a default one
     * is created. If setting an config object, then the current trait instance is returned.
     * 
     * @param ConfigInterface $config The config to set. If not set, the current config is returned.
     * 
     * @return ConfigInterface | Configurable
     */
    public function config(ConfigInterface $config = null)
    {
        // set config
        if ($config) {
            $this->config = $config;
            return $this;
        }

        // set if not exists
        if (!$this->config) {
            $this->config = new Config($this->getDefaultConfig());
        }

        // get config
        return $this->config;
    }

    /**
     * Returns the default configuration.
     * 
     * @return array
     */
    public function getDefaultConfig()
    {
        return isset($this->defaultConfig) ? $this->defaultConfig : [];
    }

    /**
     * Sets the default configuration.
     * 
     * @return Configurable
     */
    public function setDefaultConfig()
    {
        $this->config->import($this->getDefaultConfig());
    }
}