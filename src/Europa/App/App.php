<?php

namespace Europa\App;
use Europa\Controller\ControllerInterface;
use Europa\Exception\Exception;
use Europa\Di\ContainerInterface;

class App implements AppInterface
{
    const EVENT_ROUTE = 'route';

    const EVENT_ACTION = 'action';

    const EVENT_RENDER = 'render';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    const EVENT_ERROR = 'error';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        if (!$container->provides('Europa\App\AppConfigurationInterface')) {
            Exception::toss('The container passed to the application must provide "Europa\App\AppConfigurationInterface".');
        }

        $this->container = $container;
    }

    public function dispatch()
    {
        $this->container->get('modules')->bootstrap($this->container);

        $controller = $this->resolveController();
        $context    = $this->actionController($controller);
        $rendered   = $this->renderView($context);

        return $this->runResponse($rendered);
    }

    private function resolveController()
    {
        $this->container->get('events')->trigger(self::EVENT_ROUTE, $this->container);

        if (!$this->container->get('routers')->route($this->container->get('request'))) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->container->get('request'));
        }

        $controller = $this->container->get('request')->getParam($this->container->get('config')['controller-param']);

        if (!$this->container->get('controllers')->has($controller)) {
            Exception::toss('The controller "%s" could not be found.', $controller);
        }

        return $this->container->get('controllers')->get($controller);
    }

    private function actionController(ControllerInterface $controller)
    {
        $this->container->get('events')->trigger(self::EVENT_ACTION, $this->container, $controller);

        return $controller->__call(
            $this->container->get('request')->getParam($this->container->get('config')['action-param']),
            $this->container->get('request')->getParams()
        );
    }

    private function renderView($context)
    {
        $view    = $this->container->get('view');
        $context = $context ?: [];

        $this->container->get('events')->trigger(self::EVENT_RENDER, $this->container, $context);

        if ($this->container->get('response') instanceof HttpInterface) {
            $this->container->get('response')->setContentTypeFromView($view);
        }

        return $view->render($context);;
    }

    private function formatViewScript()
    {
        $format = $this->container->get('config')['view-script-format'];

        foreach ($this->container->get('request')->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }

    private function runResponse($rendered)
    {
        $this->container->get('response')->setBody($rendered);
        $this->container->get('events')->trigger(self::EVENT_SEND, $this->container, $rendered);
        $this->container->get('response')->send();
        $this->container->get('events')->trigger(self::EVENT_DONE, $this->container);
        return $this;
    }
}