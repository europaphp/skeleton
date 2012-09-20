App
===

The application layer serves as a component responsible for running your application.

The Default Bootstrapper
==============================

There is a default bootstrapper that you can use to bootstrap your application instead of building your own:

    <?php

    (new Europa\App\Bootstrapper)->boot($root, $config);

The `$root` argument specifies the root path of your application. All paths in the configuration are relative to this path.

The `$config` array is an array of configuration options that customise how the bootstrapper works. These options are:

- `containerConfig` The container configuration to initialise the `Europa\App\Container` container with.
- `errorLevel` The error level to use. Defaults to `E_ALL | E_STRICT`.
- `loadPaths` Array of autoload paths relative to the application root.
- `showErrors` Whether or not to display errors.
- `cliViewPath` The cli script path relative to the base view path set in the container.
- `webViewPath` The web view path relative to the base view path set in the container.
- `postBoot` A callable argument that gets called when booting is done. You can call your own bootstrapper here. It gets passed 2 arguments, the first is the application root and the second is the bootstrapper configuration.

The Default DI Container
------------------------

The default DI container is a subclass of `Europa\Di\Provider` that allows you to access certain dependencies that it automatically configures for you. The constructor gets passed a `$root` parameter that tells it what the application root is as well as a configuration array to customise how some of the dependencies are set up.

    Europa\App\Container::default([$appRootPath, $configArray]);

By simply accessing the container we want and pass it some configurations, we can ensure that the container is already set up before we access it to attempt to get any dependencies out of it. This means you can configure your container in your bootstrapping process before you access it somewhere in your business logic.

If you want to pre-configure multiple instances of the same container, all you have to do is specify which instance you want to initialise:

    Europa\App\Container::myContainerName([$appRootPath, $configArray]);

Which you can access like:

    Europa\App\Container::myContainerName()->someDependency->someMethod();

If for some reason you need to reconfigure it, you just access it with some arguments:

    Europa\App\Container::myContainerName([$newAppRootPath, $newConfigArray]);

### Available Dependencies

The default container gives you a bunch of dependencies that it can automatically configure and give you.

#### Application Runner `Europa\App\App app`

The default application runner.

    $container->app->run();

#### Controller Finder `Europa\Di\Finder controllers`

The controller DI finder.

    $container->controllers->index->action();

#### Helper Finder `Europa\Di\Finder helpers`

The view helper DI finder.

    echo $container->helpers->js('path/to/js/file');

#### Language File Locator `langLocator`

The language file locator.

    echo $container->langLocator->locate('web/index');

#### Request `Europa\Request\RequestInterface request`

Returns a request instance based on which interface (cli / http) you are using.

    $container->request instanceof Europa\Request\Http;

#### Cli Request `Europa\Request\Cli requestCli`

Returns the CLI request.

    $container->requestCli instanceof Europa\Request\Cli;

#### Http Request `Europa\Request\Http requestHttp`

Returns the HTTP request.

    $container->requestHttp instanceof Europa\Request\Http;

#### Response `Europa\Response\ResponseInterface response`

Returns a response instance based on which interface (cli / http) you are using.

    $container->response instanceof Europa\Response\Http;

#### Cli Response `Europa\Response\Cli responseCli`

Returns the CLI response.

    $container->responseCli instanceof Europa\Response\Cli;

#### Http Response `Europa\Response\Http responseHttp`

Returns the HTTP response.

    $container->responseHttp instanceof Europa\Response\Http;

#### Router `Europa\Router\RouterInterface router`

Returns a router instance based on which interface (cli / http) you are using.

    $container->router->query($someCliCommandOrHttpUri);

#### Cli Router `Europa\Router\Cli routerCli`

Returns the CLI router.

    $container->routerCli->query('some cli command')

#### Http Router `Europa\Router\Http routerHttp`

Returns the HTTP router.

    $container->routerHttp->query('some/uri);

#### View Renderer `Europa\View\ViewInterface view`

Returns the appropriate view for the content type that was requested.

    echo $container->view->render($context);

#### PHP View Renderer `Europa\View\Php viewPhp`

Returns the PHP view renderer.

    echo $container->viewPhp->setScript('some/php/script')->render($context);

#### PHP View Locator `Europa\Fs\Locator viewPhpLocator`

Returns the PHP view locator.

    echo $container->viewPhpLocator->addPath('some/other/path/to/your/views');

#### JSON View Renderer `Europa\View\Json viewJson`

Returns the JSON view renderer.

    echo $container->viewJson->render($context);

#### JSONP View Renderer `Europa\View\Jsonp viewJsonp`

Returns the JSONP view renderer.

    echo $container->viewJsonp('jsonpCallback')->render($context);

#### XML View Renderer `Europa\View\Xml viewXml`

Returns the XML view renderer.

    echo $container->viewXml->render($context);

### Configuration

The following configuration options can be specified as the second argument to the default container's constructor. Any of these options relating to paths are relative to the root path specified to the container as the first argument.

- `controllerFilterConfig` The controller filter configuration used to resolve controller class names.
- `helperFilterConfig` The helper filter configuration used to resolve helper class names.
- `jsonpCallbackKey` If a content type of JSON is requested - either by using a `.json` suffix or by using an application/json` content type request header - and this is set in the request, a `Jsonp` view instance is used rather than `Json` and the value of this request parameter is used as the callback.
- `langPaths` Language paths and suffixes to supply to the language file locator.
- `viewPaths` View paths and suffixes to supply to the view script locator.
- `viewTypes` Mapping of content-type to view class mapping.

Running Your Application
------------------------

The `Europa\App\App` class was created so that there is an easy way to make the necessary components work together covering 99% of the use cases out there.

The default application ties together 5 components:

- [Controller](Controller)
- [Request](Request)
- [Response](Response)
- [Router](Router) (optional)
- [View](View) (optional)

Each component is required by the application and passed to the constructor. This should be done using a DI container.

By default, you are provided with a class called `Europa\App\Container` which contains the necessary components to easily run your app.

    <?php
    
    use Europa\App\Container;
    
    include 'boot.php';
    
    Container::get()->app->run();

If you were to set up the application class manually, you would have to set up each dependency. Using a container solves this problem and provides you with each component - including the application - ready to go out of the box.

More information on [Dependency Injection Containers](Di).

Modifying the Application During Runtime
----------------------------------------

The `Europa\App\App` class uses the `Europa\Util\Eventable` trait which enables events to be bound and triggered. It defines **8** events that can be used to modify the app during runtime. Each event is at least passed in the application instance as the first argument. The events in order of triggering are:

- `route.pre(App $app)` Called prior to routing even if no router is bound.
- `route.post(App $app)` Called after routing even if no router is bound.
- `action.pre(App $app)` Called before actioning the controller.
- `action.post(App $app, array $context)` Called after the controller is actioned. The context returned from the controller is provided.
- `render.pre(App $app, array $context)` Called before the view is rendered and after the controller is actioned.
- `render.post(App $app, string $rendered)` Called after the view view is rendered, but before it is sent. The rendered string from the view is provided.
- `send.pre(App $app, string $rendered)` Called after rendering, prior to sending the response.
- `send.post(App $app, string $rendered)` Called after sending the response.

This can be useful, for example, if you would like to add any response headers depending on which view is sent. You could bind an event to `output.pre`, detect the type of view and set appropriate headers on the response before it is sent.

You could also bind an event at any point before `render.post` to detect what type of response the request wants and set the view accordingly.

Specifying the Controller Request Parameter
-------------------------------------------

By default, the controller is resolved using the `controller` request parameter. This can be set by the router, or directly set on the request. If you need to change this to, say, `ctrl` or something else, you do so by telling the application.

    Container::get()->app->setKey('ctrl');
