<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Di\ServiceContainer;
use Europa\Di\ServiceContainerInterface;
use Europa\Exception\Exception;
use Europa\Module\Module;
use Europa\Response\HttpInterface;

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
        'basePath'            => null,
        'appPath'             => '{basePath}',
        'modules'             => [],
        'moduleAliases'       => [],
        'moduleConfigs'       => [],
        'defaultModuleConfig' => [],
        'defaultViewClass'    => 'Europa\View\Php',
        'viewScriptFormat'    => ':controller/:action',
        'viewSuffix'          => 'php'
    ];

    private $container;

    private static $instances = [];

    public function __construct($config = [])
    {
        $this->initConfig($config);
        $this->initBasePath();
        $this->initModules();
    }

    public function __invoke($return = false)
    {
        $this->container->loader->register();
        $this->container->modules->bootstrap();
        
        $controller = $this->resolveController();
        $context    = $this->actionController($controller);
        $rendered   = $this->renderView($context);

        return $this->runResponse($rendered, $return);
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

    private function initConfig($config)
    {
        $configuration = new AppConfiguration;
        $configuration->setArguments('config', $this->config, $config);

        $this->container = new ServiceContainer;
        $this->container->configure($configuration);
    }

    private function initBasePath()
    {
        if (!$this->container->config['basePath']) {
            $this->container->config['basePath'] = $this->getDefaultBasePath();
        }
    }

    private function initModules()
    {
        foreach ($this->container->config['modules'] as $name => $config) {
            $config = new Config(
                $this->container->config['defaultModuleConfig'],
                $config,
                $this->container->config['moduleConfigs'][$name]
            );

            try {
                $this->modules->offsetSet($name, new Module(
                    $this->container->config['appPath'] . '/' . $name,
                    $config
                ));
            } catch (Exception $e) {
                Exception::toss('Could not initialize module "%s" from the application config because: %s', $name, $e->getMessage());
            }
        }
    }

    private function resolveController()
    {
        $this->container->event->trigger(self::EVENT_ROUTE, $this);

        $router = $this->container->router;
        
        if (!$controller = $router($this->container->request)) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->container->request);
        }

        return $controller;
    }

    private function actionController(callable $controller)
    {
        $this->container->event->trigger(self::EVENT_ACTION, $this, $controller);

        return $controller($this->container->request);
    }

    private function renderView($context)
    {
        $context = $context ?: [];

        $this->container->event->trigger(self::EVENT_RENDER, $this, $context);

        if ($this->container->response instanceof HttpInterface) {
            $this->container->response->setContentTypeFromView($this->container->view);
        }

        $rendered = $this->container->view;
        $rendered = $rendered($context);

        return $rendered;
    }

    private function runResponse($rendered, $return)
    {
        $this->container->response->setBody($rendered);
        $this->container->event->trigger(self::EVENT_SEND, $this, $rendered);

        if (!$return) {
            $this->container->response->send();
        }
        
        $this->container->event->trigger(self::EVENT_DONE, $this);

        return $return ? $rendered : $this;
    }
}