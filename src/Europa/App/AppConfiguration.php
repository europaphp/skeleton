<?php

namespace Europa\App;
use Europa\Config\Config;
use Europa\Config\ConfigInterface;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\ServiceContainer;
use Europa\Event\Manager as EventManager;
use Europa\Exception\Exception;
use Europa\Fs\Loader;
use Europa\Fs\Locator;
use Europa\Module\Manager as ModuleManager;
use Europa\Module\ManagerInterface;
use Europa\Module\Module;
use Europa\Request\RequestAbstract;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseAbstract;
use Europa\Router\Router;
use Europa\View\HelperConfiguration;
use Europa\View\Negotiator;
use Europa\View\Php;
use Europa\View\ViewScriptInterface;

class AppConfiguration extends ConfigurationAbstract implements AppConfigurationInterface
{
    private $config = [
        'base-path'              => null,
        'events'                 => [],
        'modules'                => [],
        'module-config'          => [],
        'require-autoload-paths' => false,
        'require-view-paths'     => false,
        'view-negotiator-config' => [],
        'view-script-format'     => ':controller/:action.php'
    ];

    public function __construct($config)
    {
        $this->config = new Config($this->config, $config);

        if (!$this->config['base-path']) {
            $basePath = dirname($_SERVER['PHP_SELF']);
            $basePath = $basePath === '/' ? '.' : $basePath;
            $basePath = realpath($basePath . '/..');
            $this->config['base-path'] = $basePath;
        }
    }

    public function config()
    {
        return $this->config;
    }

    public function event(ConfigInterface $config)
    {
        $manager = new EventManager;

        foreach ($config['events'] as $event => $stack) {
            foreach ($stack as $handler) {
                $manager->bind($event, $this->ensureCallableEventHandler($event, $handler));
            }
        }

        return $manager;
    }

    public function loader(callable $loaderLocator)
    {
        $loader = new Loader;
        $loader->setLocator($loaderLocator);
        return $loader;
    }

    public function loaderLocator(ConfigInterface $config, ManagerInterface $modules)
    {
        $locator = new Locator;

        foreach ($modules as $module) {
            $locator->addPaths($module->autoloadPaths(), $config['require-autoload-paths']);
        }

        return $locator;
    }

    public function modules(ConfigInterface $config)
    {
        $manager = new ModuleManager($this);

        foreach ($config['modules'] as $name) {
            try {
                $module = new Module($config['base-path'] . '/' . $name);
                $module->config()->import($config['module-config'][$module->name()]);
                $manager->offsetSet($name, $module);
            } catch (Exception $e) {
                Exception::toss('Could not initialize module "%s" from the application config because: %s', $name, $e->getMessage());
            }
        }

        return $manager;
    }

    public function request()
    {
        return RequestAbstract::detect();
    }

    public function response()
    {
        return ResponseAbstract::detect();
    }

    public function router(ManagerInterface $modules)
    {
        $router = new Router;

        foreach ($modules as $module) {
            $router->import($module->routes());
        }

        return $router;
    }

    public function view(
        ConfigInterface $config,
        RequestInterface $request,
        callable $viewLocator,
        callable $viewHelpers,
        callable $viewNegotiator,
        callable $viewScriptFormatter
    ) {
        $view = $viewNegotiator($request);

        if ($view instanceof ViewScriptInterface) {
            $view->setScriptLocator($viewLocator);
            $view->setScript($viewScriptFormatter($config['view-script-format'], $request));
        }

        if ($view instanceof Php) {
            $view->setServiceContainer($viewHelpers);
        }

        return $view;
    }

    public function viewHelpers(Router $router)
    {
        $helpers = new ServiceContainer;
        $helpers->configure(new HelperConfiguration($router));
        return $helpers;
    }

    public function viewLocator(ConfigInterface $config, ManagerInterface $modules)
    {
        $locator = new Locator;

        foreach ($modules as $module) {
            $locator->addPaths($module->viewPaths(), $config['require-view-paths']);
        }

        return $locator;
    }

    public function viewNegotiator(ConfigInterface $config)
    {
        return new Negotiator($config['view-negotiator-config']);
    }

    public function viewScriptFormatter()
    {
        return function($format, $request) {
            if (is_callable($format)) {
                return $format($request);
            }

            foreach ($request->getParams() as $name => $param) {
                $format = str_replace(':' . $name, $param, $format);
            }

            return $format;
        };
    }

    private function ensureCallableEventHandler($event, $handler)
    {
        if (is_string($handler) && class_exists($handler)) {
            return new $handler;
        }

        if (!is_callable($handler)) {
            Exception::toss(
                'The event handler "%s" for the event "%s" is not callable.',
                $handler,
                $event
            );
        }

        return $handler;
    }
}