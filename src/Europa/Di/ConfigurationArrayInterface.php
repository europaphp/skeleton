<?php

namespace Europa\Di;

interface ConfigurationArrayInterface extends ConfigurationInterface
{
    public function add(ConfigurationInterface $configuration);
}