<?php

namespace Europa\Application;

class ConfiguratorArray implements ConfiguratorInterface
{
    private $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function configure(Container $container)
    {
        foreach ($this->config as $class => $args) {
            // if the class isn't specified as the key, assume the value
            if (is_numeric($class)) {
                $class = $args;
                $args  = array();
            }
            
            // if it still is not found, then we have issues
            if (!is_string($class)) {
                throw new \InvalidArgumentException('ConfiguratorArray configuration must either specify a classname as a key or value.');
            }
            
            // we ensure that it is a valid instance
            $config = new \ReflectionClass($class);
            $iface  = __NAMESPACE__ . '\ConfiguratorInterface';
            if (!$config->implementsInterface($iface)) {
                throw new \InvalidArgumentException("Configurator {$class} must implement {$iface}.");
            }
            
            // only call a constructor with arguments if it has one
            if ($config->hasMethod('__construct')) {
                $config = $config->newInstanceArgs($args);
            } else {
                $config = $config->newInstance();
            }
            
            // configure the container
            $config->configure($container);
        }
    }
}