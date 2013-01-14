<?php

namespace Europa\App;
use Europa\Di\ServiceContainer;
use Europa\Di\ServiceContainerInterface;
use Europa\Exception\Exception;
use Europa\Response\HttpResponseInterface;

class App implements AppInterface
{
    const DEFAULT_MODULE = 'main';

    const DEFAULT_INSTANCE = 'default';

    const EVENT_ROUTE = 'route';

    const EVENT_ACTION = 'action';

    const EVENT_RENDER = 'render';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    private $config = [
        'basePath'         => null,
        'appPath'          => '{basePath}/app',
        'modules'          => [self::DEFAULT_MODULE],
        'defaultViewClass' => 'Europa\View\Php',
        'viewScriptFormat' => ':controller/:action',
        'viewSuffix'       => 'php'
    ];

    private $container;

    private static $instances = [];

    public function __construct($config = [])
    {
        $configuration = new AppConfiguration;
        $configuration->setArguments('config', $this->config, $config);

        $this->container = new ServiceContainer;
        $this->container->configure($configuration);

        if (!$this->container->config['basePath']) {
            $this->container->config['basePath'] = $this->getDefaultBasePath();
        }

        foreach ($this->container->config['modules'] as $name => $module) {
            $this->container->modules->offsetSet($name, $module);
        }
    }

    public function __invoke($return = false)
    {
        $this->container->loader->register();
        $this->container->loader->setLocator($this->container->loaderLocator);
        $this->container->modules->bootstrap($this->container);
        $this->container->event->trigger(self::EVENT_ROUTE, $this);

        $router = $this->container->router;
        
        if (!$controller = $router($this->container->request)) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->container->request);
        }

        $this->container->event->trigger(self::EVENT_ACTION, $this, $controller);

        $context = $controller($this->container->request);

        $this->container->event->trigger(self::EVENT_RENDER, $this, $context);

        if ($this->container->response instanceof HttpResponseInterface) {
            $this->container->response->setContentTypeFromView($this->container->view);
        }

        $rendered = $this->container->view;
        $rendered = $rendered($context ?: []);

        $this->container->response->setBody($rendered);
        $this->container->event->trigger(self::EVENT_SEND, $this, $rendered);

        if (!$return) {
            $this->container->response->send();
        }
        
        $this->container->event->trigger(self::EVENT_DONE, $this);

        return $return ? $rendered : $this;
    }

    public function __set($name, $service)
    {
        $this->container->__set($name, $service);
    }

    public function __get($name)
    {
        return $this->container->__get($name);
    }

    public function __isset($name)
    {
        return $this->container->__isset($name);
    }

    public function __unset($name)
    {
        $this->container->__unset($name);
    }

    public function setServiceContainer(ServiceContainerInterface $container)
    {
        $this->container = $container->mustProvide('Europa\App\AppConfigurationInterface');
        return $this;
    }

    public function getServiceContainer()
    {
        return $this->container;
    }

    public function save($name = self::DEFAULT_INSTANCE)
    {
        self::$instances[$name] = $this;
        return $this;
    }

    public function offsetSet($name, $module)
    {
        $this->container->modules->offsetSet($name, $module);
        return $this;
    }

    public function offsetGet($name)
    {
        return $this->container->modules->offsetGet($name);
    }

    public function offsetExists($name)
    {
        return $this->container->modules->offsetExists($name);
    }

    public function offsetUnset($name)
    {
        $this->container->modules->offsetUnset($name);
        return $this;
    }

    public function count()
    {
        return $this->container->modules->count();
    }

    public function getIterator()
    {
        return $this->container->modules->getIterator();
    }

    public static function get($name = self::DEFAULT_INSTANCE)
    {
        if (!isset(self::$instances[$name])) {
            Exception::toss('Could not find application instance "%s".', $name);
        }

        return self::$instances[$name];
    }

    private function getDefaultBasePath()
    {
        $script = dirname($_SERVER['PHP_SELF']);
        $script = $script === '/' ? '.' : $script;

        return realpath($script . '/..');
    }
}