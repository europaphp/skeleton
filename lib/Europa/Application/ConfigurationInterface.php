<?php

namespace Europa\Application;

interface ConfigurationInterface
{
	/**
	 * Configures the specified container.
	 * 
	 * @param \Europa\Application\Container $container The container to configure.
	 * 
	 * @return void
	 */
    public function configure(Container $container);
}
