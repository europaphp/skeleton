<?php

namespace Europa\App;
use Europa\Boot\Files;
use Europa\Boot\Provider;
use Europa\Fs\Finder;
use Europa\View\ViewScriptInterface;

/**
 * The default application bootstrapper. Works in conjunction with Europa\App\App and Europa\App\Container.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Boot extends Provider
{
    /**
     * The root path. All paths specified in the config are relative to this.
     * 
     * @var string
     */
    private $root;

    /**
     * The container that is set up.
     * 
     * @var Container
     */
    private $container;

    /**
     * The default configuration.
     * 
     * - `appPath` The application path containing the modules relative to the root path.
     * - `bootFile` The optional file that is included for each module at the end of bootstrapping.
     * - `classPaths` The class paths relative to each module.
     * - `containerName` The name of the DI container to configure.
     * - `containerConfig` The container configuration to initialise the container with.
     * - `errorLevel` The error level to use. Defaults to `E_ALL | E_STRICT`.
     * - `langPaths` The language file paths relative to each module.
     * - `modules` The modules to load. Values correspond to each module's directory name.
     * - `showErrors` Whether or not to display errors.
     * - `viewPaths` The view file paths relative to each module.
     * 
     * @var array
     */
    private $config = [
        'appPath'    => 'app',
        'bootFile'   => 'boot.php',
        'classPaths' => ['classes' => 'php'],
        'containerName'    => 'europa',
        'containerConfig'  => [
            'classPaths' => [],
            'langPaths'  => [],
            'viewPaths'  => []
        ],
        'errorLevel' => null,
        'langPaths'  => ['langs/en-us' => 'ini'],
        'modules'    => [],
        'showErrors' => true,
        'viewPaths'  => ['views' => 'php']
    ];

    /**
     * Sets up the bootstrapper.
     * 
     * @param string $root The application root.
     * @param array  $config The bootstrapper configuration.
     * 
     * @return Boot
     */
    public function __construct($root, array $config = [])
    {
        // set app root path
        $this->root = realpath($root);

        // root must be valid
        if (!$this->root) {
            throw new UnexpectedValueException(sprintf('The root path "%s" is not valid.', $root));
        }

        // set default config options that can't be set in the array
        $this->config['errorLevel'] = E_ALL | E_STRICT;

        // merge configurations
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Sets whether or not errors should be displayed and the desired error level.
     * 
     * @return void
     */
    public function errorReporting()
    {
        ini_set('display_errors', $this->config['showErrors'] ? 'on' : 'off');
        error_reporting($this->config['errorLevel']);
    }

    /**
     * Adds appropriate paths to the container config so that all dependencies are notified of each modules paths.
     * 
     * @return void
     */
    public function setUpContainerConfigForModules()
    {
        foreach ($this->config['modules'] as $module) {
            $modulePath = $this->config['appPath'] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;

            foreach (['classPaths', 'langPaths', 'viewPaths'] as $config) {
                foreach ($this->config[$config] as $path => $suffix) {
                    $this->config['containerConfig'][$config][$modulePath . $path] = $suffix;
                }
            }
        }
    }

    /**
     * Sets up the default container.
     * 
     * @return void
     */
    public function setUpContainer()
    {
        $this->container = Container::{$this->config['containerName']}($this->root, $this->config['containerConfig']);
    }

    /**
     * Registers autoloading using the container loader.
     * 
     * @return void
     */
    public function registerAutoloading()
    {
        $this->container->loader->register();
    }

    /**
     * Finally, we bootstrap modules.
     * 
     * @return void
     */
    public function bootstrapModules()
    {
        foreach ($this->config['modules'] as $module) {
            $bootFile = $this->root
                . DIRECTORY_SEPARATOR
                . $this->config['appPath']
                . DIRECTORY_SEPARATOR
                . $module
                . DIRECTORY_SEPARATOR
                . $this->config['bootFile'];

            if (is_file($bootFile)) {
                require_once $bootFile;
            }
        }
    }
}