<?php

namespace Europa\App;
use Europa\Config\ConfigInterface;
use Europa\Controller\ControllerInterface;
use Europa\Di\ResolverInterface;
use Europa\Event\EmitterInterface;
use Europa\Exception\Exception;
use Europa\Module\ManagerInterface;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;

class App
{
    const EVENT_ROUTE = 'route';

    const EVENT_RENDER = 'render';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    private $config;

    private $events;

    private $modules;

    private $request;

    private $response;

    private $router;

    private $views;

    public function __construct(
        ConfigInterface $config,
        EmitterInterface $events,
        ManagerInterface $modules,
        RequestInterface $request,
        ResponseInterface $response,
        callable $router,
        callable $views
    ) {
        $this->config   = $config;
        $this->events   = $events;
        $this->modules  = $modules;
        $this->request  = $request;
        $this->response = $response;
        $this->router   = $router;
        $this->views    = $views;
    }

    public function __invoke()
    {
        $this->modules->bootstrap();
        $this->events->emit(self::EVENT_ROUTE);

        return $this->runResponse(
            $this->renderView(
                $this->router->__invoke($this->request)
            )
        );
    }

    private function renderView($context)
    {
        $view    = $this->views;
        $view    = $view();
        $context = $context ?: [];

        $this->events->emit(self::EVENT_RENDER, $context, $this->response, $view);

        if ($this->response instanceof HttpInterface) {
            $this->response->setContentTypeFromView($view);
        }

        return $view->render($context);
    }

    private function runResponse($rendered)
    {
        $this->response->setBody($rendered);
        $this->events->emit(self::EVENT_SEND, $this->response);

        $this->response->send();
        $this->events->emit(self::EVENT_DONE, $this->response);

        return $this;
    }
}