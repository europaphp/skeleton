<?php

namespace Europa\Di\Configuration;
use Europa\Di\Container;

interface ConfigurationInterface
{
	/**
	 * Configures the specified container.
	 * 
	 * @param \Europa\Di\Container $container The container to configure.
	 * 
	 * @return void
	 */
    public function configure(Container $container);
}
