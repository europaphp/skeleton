<?php

namespace Europa\App;
use Closure;
use Europa\Config\ConfigInterface;
use Europa\Controller\ControllerInterface;
use Europa\Di\ResolverInterface;
use Europa\Event\ManagerInterface as EventManagerInterface;
use Europa\Exception\Exception;
use Europa\Module\ManagerInterface as ModuleManagerInterface;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;

class App implements AppInterface
{
    const EVENT_ROUTE = 'route';

    const EVENT_ACTION = 'action';

    const EVENT_RENDER = 'render';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    const EVENT_ERROR = 'error';

    private $config;

    private $controllers;

    private $events;

    private $modules;

    private $request;

    private $response;

    private $router;

    private $views;

    public function __construct(
        ConfigInterface $config,
        ResolverInterface $controllers,
        EventManagerInterface $events,
        ModuleManagerInterface $modules,
        RequestInterface $request,
        ResponseInterface $response,
        RouterInterface $router,
        Closure $views
    ) {
        $this->config      = $config;
        $this->controllers = $controllers;
        $this->events      = $events;
        $this->modules     = $modules;
        $this->request     = $request;
        $this->response    = $response;
        $this->router      = $router;
        $this->views       = $views;
    }

    public function dispatch()
    {
        $this->modules->bootstrap();

        $controller = $this->resolveController();
        $context    = $this->actionController($controller);
        $rendered   = $this->renderView($context);

        return $this->runResponse($rendered);
    }

    private function resolveController()
    {
        $this->events->trigger(self::EVENT_ROUTE, $this->controllers, $this->request, $this->router);

        if (!$this->router->route($this->request)) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->request);
        }

        $controller = $this->request->getParam($this->config['controller-param']);

        if (!$this->controllers->has($controller)) {
            Exception::toss('The controller "%s" could not be found.', $controller);
        }

        return $this->controllers->get($controller);
    }

    private function actionController(ControllerInterface $controller)
    {
        $this->events->trigger(self::EVENT_ACTION, $controller, $this->request);

        return $controller->__call(
            $this->request->getParam($this->config['action-param']),
            $this->request->getParams()
        );
    }

    private function renderView($context)
    {
        $view    = $this->views;
        $view    = $view();
        $context = $context ?: [];

        $this->events->trigger(self::EVENT_RENDER, $context, $this->response, $view);

        if ($this->response instanceof HttpInterface) {
            $this->response->setContentTypeFromView($view);
        }

        return $view->render($context);
    }

    private function runResponse($rendered)
    {
        $this->response->setBody($rendered);
        $this->events->trigger(self::EVENT_SEND, $this->response);
        $this->response->send();
        $this->events->trigger(self::EVENT_DONE, $this->response);
        return $this;
    }
}