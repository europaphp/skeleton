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
     * Event fired before routing.
     * 
     * @var string
     */
    const EVENT_ROUTE = 'route';

    /**
     * Event fired before actioning.
     * 
     * @var string
     */
    const EVENT_ACTION = 'action';

    /**
     * Event fired before rendering.
     * 
     * @var string
     */
    const EVENT_RENDER = 'render';

    /**
     * Event fired before sending the response.
     * 
     * @var string
     */
    const EVENT_SEND = 'send';

    /**
     * Event fired before returning.
     * 
     * @var string
     */
    const EVENT_DONE = 'done';

    /**
     * The application configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'container'        => 'europa',
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
        // Set up the container configuration.
        $configuration = new AppConfiguration;
        $configuration->setArguments('config', $this->config, $config);

        // Configure the container.
        $this->container = new ServiceContainer;
        $this->container->configure($configuration);
        
        // Save the container as the one specified in the configuration.
        $this->container->save($this->container->config->container);
    }

    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke()
    {
        // Register autloading and set autoload paths.
        $this->container->loader->register();
        $this->container->loader->setLocator($this->container->loaderLocator);
        
        // Bootstrap the modules.
        $this->container->modules->bootstrap($this->container);

        // Trigger the `route` event prior to routing.
        $this->container->event->trigger(self::EVENT_ROUTE, $this);
        
        // Route and get a controller. If no controller is found, throw an exception
        if (!$controller = $this->container->router($this->container->request)) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->container->request);
        }

        // The `action` event gets triggered before the controller is actioned.
        $this->container->event->trigger(self::EVENT_ACTION, $this, $controller);

        // Get the render context from the return value of the action so it can be passed to the `render` event and view.
        $context = $controller($this->container->request);

        // The `render` event gets triggered before the content type is set on the response.
        $this->container->event->trigger(self::EVENT_RENDER, $this, $context);

        // Set the response content type only if an HTTP response is being used.
        if ($this->container->response instanceof HttpResponseInterface) {
            $this->container->response->setContentTypeFromView($this->container->view);
        }

        // Get the rendered content from the view.
        $rendered = $this->container->view($context ?: []);

        // Set the response body from the rendered view.
        $this->container->response->setBody($rendered);

        // The `send` event gets triggered prior to sending.
        $this->container->event->trigger(self::EVENT_SEND, $this, $rendered);

        // Send the response to the client.
        $this->container->response->send();

        // Trigger the `done` event after everything is dispatched.
        $this->container->event->trigger(self::EVENT_DONE, $this);

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