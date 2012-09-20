<?php

namespace Europa\App;
use Europa\Boot\BootClass;
use Europa\Fs\Loader;
use Europa\View\ViewScriptInterface;

/**
 * The default application bootstrapper. Works in conjunction with Europa\App\App and Europa\App\Container.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Boot extends BootClass
{
    /**
     * The root path. All paths specified in the config are relative to this.
     * 
     * @var string
     */
    private $root;

    /**
     * The default configuration.
     * 
     * - containerConfig: The container configuration to initialise the container with.
     * - errorLevel: The error level to use. Defaults to "E_ALL | E_STRICT".
     * - loadPaths: Array of autoload paths relative to the application root.
     * - showErrors: Whether or not to display errors.
     * - cliViewPath: The cli script path relative to the base view path set in the container.
     * - webViewPath: The web view path relative to the base view path set in the container.
     * - postBoot: A callable argument that gets called when booting is done. You can call your own bootstrapper here.
     * 
     * @var array
     */
    private $config = [
        'containerConfig' => [],
        'errorLevel' => null,
        'loadPaths' => [
            'app/classes'
        ],
        'showErrors' => true,
        'cliViewPath' => 'cli',
        'webViewPath' => 'web',
        'postBoot' => null
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
     * Registers autoloading using the specified load paths. Load paths are specified relative to the root path that
     * was given.
     * 
     * @return void
     */
    public function registerAutoloading()
    {
        $loader = new Loader;
        $loader->getLocator()->setBasePath($this->root)->addPaths($this->config['loadPaths']);
        $loader->register();
    }

    /**
     * Sets up the default container.
     * 
     * @return void
     */
    public function setUpContainer()
    {
        Container::init([$this->root, $this->config['containerConfig']]);
    }

    /**
     * Binds an event to the application to set the appropriate view script after rendering, but only if the view
     * is an instance of ViewScriptInterface.
     * 
     * @return void
     */
    public function setViewAfterRendering()
    {
        Container::get()->app->event()->bind('route.post', function($app) {
            if ($app->getView() instanceof ViewScriptInterface) {
                $script = $app->getRequest()->isCli() ? $this->config['cliViewPath'] : $this->config['webViewPath'];
                $script = $script . '/' . $app->getRequest()->controller;
                $script = str_replace(' ', '/', $script);
                $app->getView()->setScript($script);
            }
        });
    }

    /**
     * Executes a specified handler after all default booting occurs. This makes it easier to boot a custom app by
     * simply using your own booter, rather than having to set up autoloading, etc, then use your booter.
     * 
     * @return void
     */
    public function postBoot()
    {
        if (is_callable($this->config['postBoot'])) {
            call_user_func($this->config['postBoot'], $this->root, $this->config);
        }
    }
}