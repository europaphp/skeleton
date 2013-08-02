<?php

namespace Europa\App;
use Europa\Module;
use Europa\Request;
use Europa\Response;

class Dispatcher
{
  private $controllerCaller;

  private $controllerResolver;

  private $moduleManager;

  private $request;

  private $response;

  private $router;

  private $viewRenderer;

  public function __construct(
    callable $controllerCaller,
    callable $controllerResolver,
    Module\ManagerInterface $moduleManager,
    Request\RequestInterface $request,
    Response\ResponseInterface $response,
    callable $router,
    callable $view,
    callable $viewFilter
  ) {
    $this->controllerCaller = $controllerCaller;
    $this->controllerResolver = $controllerResolver;
    $this->moduleManager = $moduleManager;
    $this->request = $request;
    $this->response = $response;
    $this->router = $router;
    $this->view = $view;
    $this->viewFilter = $viewFilter;
  }

  public function __invoke($return = false)
  {
    $this->moduleManager->bootstrap();

    if (!$controller = call_user_func($this->router, $this->request)) {
      throw new Exception\NoController([
        'request' => $this->request
      ]);
    }

    $controller = call_user_func($this->controllerResolver, $controller);

    call_user_func($this->viewFilter, $this->view, $controller);

    $body = call_user_func(
      $this->view,
      call_user_func($this->controllerCaller, $controller)
    );

    if ($return) {
      return $body;
    }

    $this->response->setBody($body);
    $this->response->send();
  }
}