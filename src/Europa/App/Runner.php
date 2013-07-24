<?php

namespace Europa\App;
use Europa\Event\EmitterInterface;
use Europa\Module\ManagerInterface;
use Europa\Request\RequestInterface;

class Runner
{
    const EVENT_ROUTE = 'route';

    const EVENT_SEND = 'send';

    const EVENT_DONE = 'done';

    private $events;

    private $modules;

    private $request;

    private $router;

    public function __construct(
        EmitterInterface $events,
        ManagerInterface $modules,
        RequestInterface $request,
        callable $router
    ) {
        $this->events  = $events;
        $this->modules = $modules;
        $this->request = $request;
        $this->router  = $router;
    }

    public function __invoke()
    {
        $this->modules->bootstrap();
        $this->events->emit(self::EVENT_ROUTE, $this->modules, $this->request);

        $router = $this->router;

        if (!$response = $router($this->request)) {
            throw new Exception\NoResponse(['request' => $this->request]);
        }

        $this->events->emit(self::EVENT_SEND, $this->modules, $this->request, $response);
        $response();
    }
}