Request
=======

The request is the front door into the framework. It allows you to make both HTTP and CLI requests. All interaction between which interface was used is abstracted by using the proper request for the proper interface.

All built-in requests extend `RequestAbstract`. All requests *must* implement `RequestInterface` at a bare minimum.

Since requests are abstracted this way, you can make both a command line request or a web request using the same infrastructure.

HTTP Requests
-------------

HTTP requests are done using the `Http` class. By default, if the request is instantiated in an HTTP environment, then it will initialise itself to represent the current request that is being made. If we take the following request for example:

    GET http://localhost:8080/some/path?name1=value1&name2=value2#some-fragment

If you instantiated a request when this request was made, it would initialise all of this information:

    <?php
    
    use Europa\Request\Http;
    
    $request = new Http;
    
    // "get"
    $request->getMethod();
    
    // "value1"
    $request->name1;
    
    // "some-fragment"
    $request->getUri()->getFragment();

### The Uri Class and Mocking

If you need to mock a particular request, you can:

    <?php
    
    use Europa\Request\Http;
    use Europa\Request\Uri;
    
    $request = new Http;
    $request->setMethod(Http::GET);
    $request->setUri('http://localhost:8080/some/path?name1=value1&name2=value2#some-fragment');

The `Uri` class allows you to do anything you want to it:

    <?php
    
    use Europa\Request\Uri;
    
    $uri = new Uri;
    
    // params
    $uri->name1 = 'value1';
    $uri->name2 = 'value2';
    $uri->setParam('name1', 'value1')->setParam('name2', 'value2')->setParams([
        'name1' => 'value1',
        'name2' => 'value2
    ]);

    // "value1"
    $uri->name1;
    $uri->getParam('name1');
    
    // false
    $uri->removeParam('name2')->hasParam('name2');
    
    unset($uri->name1);
    
    // false
    isset($uri->name1);
    
    // remove all
    $uri->removeParams();
    
    // "http"
    $uri->setScheme('http')->getScheme();
    
    // "http://"
    $uri->getSchemePart();
    
    // "me"
    $uri->setUsername('me')->getUsername();
    
    // "password"
    $uri->setPassword('password')->getPassword();
    
    // "myhost.com"
    $uri->setHost('myhost.com')->getHost();
    
    // "8080"
    $uri->setPort(8080)->getPort();
    
    // ":8080"
    $uri->getPortPart();
    
    // "http://me:password@myhost.com:8080"
    $uri->getHostPart();
    
    // "root"
    $uri->setRoot('root')->getRoot();
    
    // "/root"
    $uri->getRootPart();
    
    // "request/uri"
    $uri->setRequest('/request/uri/')->getRequest();
    
    // "/root/request/uri"
    $uri->getRequestPart();
    
    // "html"
    $uri->setSuffix('html')->getSuffix();
    
    // ".html"
    $uri->getSuffixPart();
    
    // "name1=value1&name2=value2"
    $uri->setQuery('?name1=value1&name2=value2')->getQuery();
    
    // "?name1=value1&name2=value2"
    $uri->getQueryPart();
    
    // "frag"
    $uri->setFragment('#frag')->getFragment();
    
    // "#frag"
    $uri->getFragmentPart();
    
    // "http://me:password@myhost.com:8080/foot/request/uri?name1=value1&name2=value2#frag"
    $uri->__toString();

You can also detect the current URI if one exists:

    // "http://me:password@myhost.com:8080/foot/request/uri?name1=value1&name2=value2#frag"
    echo Uri::detect();

And redirect to it:

    $uri->redirect():

CLI Requests
------------

CLI requests are done using the `Cli` class. This great because, you can make a command line request and use the same code path as you would with web requests. You can even apply routers to CLI requests in the same fashion as you can with HTTP requests. Just your route expression changes. Take the following CLI request:

    www/index.php some command --name value --flag

The `Cli` request then initialises itself with that information and you can use it right away:

    <?php
    
    use Europa\Request\Cli;
    
    $cli = new Cli;
    
    // "value"
    $cli->name;
    
    // true
    $cli->flag;
    
    // "some command"
    $cli->getCommand();
    
    // ["some", "command"]
    $cli->getCommands();

### Mocking

It's also easy to mock a CLI request:

    <?php
    
    use Europa\Request\Cli;
    
    $cli = new Cli;
    
    // parameters
    $cli->name = 'value';
    $cli->flag = true;
    
    // via string
    $cli->setCommand('some command');
    
    // via array
    $cli->setCommands(['some', 'command']);
