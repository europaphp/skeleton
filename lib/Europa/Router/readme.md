Router
======

The `Router` component exists to route an instance of `Europa\Request\RequestInterface`. It does so by setting the parameters matched by the router onto the request.

The Default Router
------------------

The default router takes one or more routes that implement `RouteInterface`. These routes are responsible for returning any parameters found in a query which is derived from the request. By default, the router queries the route by using the `__toString()` method on the request.

    <?php
    
    use Europa\Request\Http;
    use Europa\Router\Router;
    
    $http   = new Http;
    $router = new Router;
    
    $index    = new RegexRoute('^$', '', ['controller' => 'index']);
    $notFound = new RegexRoute('.*', ':controller', ['controller' => 'not-found']);
    
    $router->setRoute('index, $index)->setRoute('not-found', $notFound);
    $router->route($http);
    
    // "index"
    $http->controller;

Since the router matches routes in the order in which they were applied, we must be careful not to mix up our order:

    $router->setRoute('not-found', $notFound)->setRoute('index, $index);
    $router->route($http);
    
    // "not-found"
    $http->controller;

### The Regular Expression Route

The regular expression route takes an expression in the form of a PCRE regex. It automates the start / end delimiters using a hash ("#"). Since regular expressions are difficult to reverse engineer, we specify a simple pattern as the second argument. For the third, we specify default parameters to set if none are matched.

    <?php
    
    use Europa\Router\RegexRoute;
    
    $regex = new Regex(
        '^(?<controller>[^/]+)/(?<action>[^/]+)/(?<id>[^/]+),
        ':controller/:action/:id',
        [
            'controller' => 'index',
            'action'     => 'get'
        ]
    );
    
    // ['controller' => 'request', 'action' => 'uri', 'id' => '1']
    $regex->query('request/uri/1');

Using the expression we set as the second parameter, we can reverse engineer the route:

    // 'index/get/2'
    $regex->format(['id' => 2]);

At the bare minimum, we don't need to specify the default parameters. However, we did need to specify an `id` otherwise we would get this:

    // 'index/get/:id'
    $regex->format();

Or we can specify all params:

    // 'user/post/userid'
    $regex->format([
        'controller' => 'user',
        'action'     => 'post',
        'id'         => 'userid'
    ]);


### The Token Route

The token route simplifies upon the `RegexRoute` by only taking a single expression for both matching and reverse engineering. The second parameter is the defaults.

    <?php
    
    use Europa\Router\TokenRoute;
    
    $token = new Token(':controller/:action/:id');
    
    // 'user/post/userid'
    $regex->format([
        'controller' => 'user',
        'action'     => 'post',
        'id'         => 'userid'
    ]);

Getting More Out of Your Router
-------------------------------

You can do more than simply set routes and route a request. You can also get routes, check if a route exists and remove routes. It also allows you to reverse engineer a route based on the route name and customise how a request is queried.

For managing routes:

    <?php
    
    use Europa\Request\Http;
    use Europa\Router\Router;
    
    $router = new Router;
    $router->setRoute('index', new TokenRoute(''));
    $router->setRoute('user', new TokenRoute(':user'));
    
    // false
    $router->removeRoute('index')->hasRoute('index');

For reverse engineering a route:

    // "me"
    $router->format('user', ['user' => 'me']);

Changing how a request is queried:

    // Match the whole URL instead of just part of it
    $router->filter(function($request) {
        if ($request instanceof Http) {
            return $request->getUri()->__toString();
        }
        return $request->__toString();
    });

Custom Routes
-------------

If the built-in routes don't do what you want, all you have to do is implement the `RouteInterface` and you can use them the same way as any existing route.

Custom Routers
--------------

If writing your own routes still doesn't cut the mustard, you can write your own router by implementing the `RouterInterface`. This is especially useful if you need to query the database such as with a CMS.
