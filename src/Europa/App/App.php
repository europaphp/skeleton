<?php

namespace Europa\App;
use Europa\Di\ServiceContainer;
use Europa\Di\ServiceContainerInterface;
use Europa\Exception\Exception;
use Europa\Response\HttpResponseInterface;

/**
 * Default application runner implementation.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class App implements AppInterface
{
    /**
     * The application configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'appPath'          => '../app',
        'defaultViewClass' => 'Europa\View\Php',
        'viewScriptFormat' => ':controller/:action',
        'viewSuffix'       => 'php'
    ];

    /**
     * The service container responsible for providing the services necessary to run the application.
     * 
     * @var ServiceContainerInterface
     */
    private $container;

    /**
     * Sets up a new application.
     * 
     * @param array | object $config The application configuration.
     * 
     * @return App
     */
    public function __construct($config = [])
    {
        $configuration = new AppConfiguration;
        $configuration->setArguments('config', $this->config, $config);

        $this->container = new ServiceContainer;
        $this->container->configure($configuration);
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        $this->container->loader->register();
        $this->container->loader->setLocator($this->container->loaderLocator);
        
        if (isset($this->container->modules)) {
            $this->container->modules->bootstrap($this->container);
        }
        
        if (!$controller = $this->container->router($this->container->request)) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->container->request);
        }

        if ($this->container->response instanceof HttpResponseInterface) {
            $this->container->response->setContentTypeFromView($this->container->view);
        }

        $this->container->response->setBody($this->container->view($controller($this->container->request) ?: []));
        $this->container->response->send();

        return $this;
    }

    /**
     * Sets the service container to use.
     * 
     * @param ServiceContainerInterface $container The service container.
     * 
     * @return App
     */
    public function setServiceContainer(ServiceContainerInterface $container)
    {
        $this->container = $container->mustProvide('Europa\App\AppConfigurationInterface');
        return $this;
    }

    /**
     * Returns the service container.
     * 
     * @return ServiceContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->container;
    }
}