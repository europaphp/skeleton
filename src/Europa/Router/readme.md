Router
======

It is the router's job to take a request and return a controller capable of handling that request. The default router does this by using routes.

Adding Routes
-------------

By default, you can use the `Europa\Router\Router` class for routing. It allows you to apply multiple routes to it. When you invoke it, it matches each route against the request and returns a controller if a route was matched.

    $router = new Europa\Router\Router;

### Programatically

There are multiple ways to add routes. Firstly, you can add a `Europa\Router\Route` instance.

    $router['my-route'] = new Europa\Router\Route;

You can also pass in a closure.

    $router['my-route'] = function($request) {
        $controller = 'Controller\\' . $request->getParam('controller');
        return new $controller;
    };

Another way is to pass in a route configuration. It will automatically instantiate a `Europa\Router\Route` object and pass the configuration to it.

    $router['my-route'] = [
        'match'             => '^$',
        'method'            => 'get',
        'format'            => ':controller/:action',
        'params'            => ['controller' => 'index', 'action' => 'get'],
        'controller.prefix' => 'Controller\\',
        'controller.suffix' => ''
    ];

### Using Configuration Files

Probably the simplest and most maintainable way to add routes is to specify a file in which your routes can be loaded from.

Since routes use the `Europa\Config\Config` class when handling the passed in configuration, you can simply pass the file you wish to use to the `import()` method on the router.

Take a sample JSON file:

    {
        "my-route": {
            "match": '^$',
            "method": 'get',
            "format": ':controller/:action',
            "params": {
                "controller": "index",
                'action" => "get"
            },
            "controller": {
                "prefix": "Controller\\",
                "suffix": ""
            }
        }
    }

If this configuration was in `routes.json`, you could load it easily by using:

    $router->import('routes.json');

A side-effect of this is that it also supports all the same adapters that the configuration component does and reuses *a lot* of code in the process.

Route Configuration
-------------------

Routes take a configuration array to describe how the route should behave.

`match`

The regular expression to use for matching the request against.

`method`

The request method to match against.

`format`

The URI format to use for reverse engineering the route. Request parameter placeholders can be specified by prefixing the request parameter name with a `:`. For example, `:controller`.

`params`

The params to set on the request. Any parameters set here as well as the ones existing on the request are eventually passed on to the controller.

`controller.prefix`

The controller class name prefix to format the `controller` parameter with.

`controller.suffix`

The controller class name suffix to format the `controller` parameter with.

Invoking the Router
-------------------

Once you have the router set up, you can invoke it to get a controller back.

    $router = new Europa\Router\Router;
    $router['user-patch'] => [
        'match'  => '^user/(?<id>[^\d]+)$',
        'method' => 'patch',
        'format' => 'user/:id',
        'params' => [
            'controller' => 'user',
            'action'     => 'patch'
        ]
    ];

    $request = new Europa\Request\Http;
    $request->setMethod('patch');
    $request->setUri('user/1');
    $request->setParam('email', 'partial-update@email.com');
    
    $controller = $router($request);
    
    // Controller\User
    echo get_class($controller);

Using Formats for Flexibile URLs
--------------------------------

We can reverse engineer routes by using the format method on the router.

    // user/1
    echo $router->format('user-patch', [
        'id' => 1
    ]);

This makes if you need to be able to generate URLs and have the change when you update your routes.