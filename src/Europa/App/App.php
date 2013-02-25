<?php

namespace Europa\App;
use Europa\Controller\ControllerInterface;
use Europa\Exception\Exception;
use Europa\Di\Container;
use Europa\Di\DependencyInjectorAware;
use Europa\Di\DependencyInjectorAwareInterface;

class App implements AppInterface, DependencyInjectorAwareInterface
{
    use DependencyInjectorAware;

    const EVENT_ROUTE = 'route';

    const EVENT_ACTION = 'action';

    const EVENT_RENDER = 'render';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    const EVENT_ERROR = 'error';

    public function __construct()
    {
        $this->injector = (new AppConfiguration)->configure(new Container);
    }

    public function dispatch()
    {
        $this->injector->get('modules')->bootstrap($this->injector);

        $controller = $this->resolveController();
        $context    = $this->actionController($controller);
        $rendered   = $this->renderView($context);

        return $this->runResponse($rendered);
    }

    private function resolveController()
    {
        $this->injector->get('events')->trigger(self::EVENT_ROUTE, $this->injector);

        if (!$this->injector->get('router')->route($this->injector->get('request'))) {
            Exception::toss('The router could not find a suitable controller for the request "%s".', $this->injector->get('request'));
        }

        $controller = $this->injector->get('request')->getParam($this->injector->get('config')['controller-param']);

        if (!$this->injector->get('controllers')->has($controller)) {
            Exception::toss('The controller "%s" could not be found.', $controller);
        }

        return $this->injector->get('controllers')->get($controller);
    }

    private function actionController(ControllerInterface $controller)
    {
        $this->injector->get('events')->trigger(self::EVENT_ACTION, $this->injector, $controller);

        return $controller->__call(
            $this->injector->get('request')->getParam($this->injector->get('config')['action-param']),
            $this->injector->get('request')->getParams()
        );
    }

    private function renderView($context)
    {
        $view    = $this->injector->get('view');
        $context = $context ?: [];

        $this->injector->get('events')->trigger(self::EVENT_RENDER, $this->injector, $context);

        if ($this->injector->get('response') instanceof HttpInterface) {
            $this->injector->get('response')->setContentTypeFromView($view);
        }

        return $view->render($context);;
    }

    private function formatViewScript()
    {
        $format = $this->injector->get('config')['view-script-format'];

        foreach ($this->injector->get('request')->getParams() as $name => $param) {
            $format = str_replace(':' . $name, $param, $format);
        }

        return $format;
    }

    private function runResponse($rendered)
    {
        $this->injector->get('response')->setBody($rendered);
        $this->injector->get('events')->trigger(self::EVENT_SEND, $this->injector, $rendered);
        $this->injector->get('response')->send();
        $this->injector->get('events')->trigger(self::EVENT_DONE, $this->injector);
        return $this;
    }
}