Request
=======

The request is the front door into the framework. It allows you to make both HTTP and CLI requests. All interaction between which interface was used is abstracted by using the proper request for the proper interface.

All built-in requests extend `RequestAbstract`. All requests *must* implement `RequestInterface` at a bare minimum.

Since requests are abstracted this way, you can make both a command line request or a web request using the same controllers.

HTTP Requests
-------------

HTTP requests are done using the `Europa\Request\Http` class. By default, if the request is instantiated in an HTTP environment, then it will initialise itself to represent the current request that is being made. If we take the following request for example:

    GET http://localhost:8080/some/path?name1=value1&name2=value2

If you instantiated a request when this request was made, it would initialise all of this information:
    
    $request = new Europa\Request\Http;
    
    // "get"
    $request->getMethod();
    
    // "value1"
    $request->name1;

    // "value2"
    $request->name2;

### The Uri Class and Mocking

If you need to mock a particular request, you can:
    
    $request = new Europa\Request\Http;
    $request->setMethod('get');
    $request->setUri('http://localhost:8080/some/path?name1=value1&name2=value2#some-fragment');

The `Uri` class allows you to do pretty much anything you want to it:
    
    $uri = new Europa\Request\Uri;
    
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
    echo $uri->setScheme('http')->getScheme();
    
    // "http://"
    echo $uri->getSchemePart();
    
    // "me"
    echo $uri->setUsername('me')->getUsername();
    
    // "password"
    echo $uri->setPassword('password')->getPassword();
    
    // "myhost.com"
    echo $uri->setHost('myhost.com')->getHost();
    
    // "8080"
    echo $uri->setPort(8080)->getPort();
    
    // ":8080"
    echo $uri->getPortPart();
    
    // "http://me:password@myhost.com:8080"
    echo $uri->getHostPart();
    
    // "root"
    echo $uri->setRoot('root')->getRoot();
    
    // "/root"
    echo $uri->getRootPart();
    
    // "request/uri"
    echo $uri->setRequest('/request/uri/')->getRequest();
    
    // "/root/request/uri"
    echo $uri->getRequestPart();
    
    // "html"
    echo $uri->setSuffix('html')->getSuffix();
    
    // ".html"
    echo $uri->getSuffixPart();
    
    // "name1=value1&name2=value2"
    echo $uri->setQuery('?name1=value1&name2=value2')->getQuery();
    
    // "?name1=value1&name2=value2"
    echo $uri->getQueryPart();
    
    // "frag"
    echo $uri->setFragment('#frag')->getFragment();
    
    // "#frag"
    echo $uri->getFragmentPart();
    
    // "http://me:password@myhost.com:8080/foot/request/uri?name1=value1&name2=value2#frag"
    echo $uri;

You can also detect the current URI if one exists:

    // "http://me:password@myhost.com:8080/foot/request/uri?name1=value1&name2=value2#frag"
    echo Europa\Request\Uri::detect();

And redirect to it:

    $uri->redirect():

CLI Requests
------------

CLI requests are done using the `Europa\Request\Cli` class. This great because, you can make a command line request and use the same code path as you would with web requests. You can even apply routers to CLI requests in the same fashion as you can with HTTP requests. Just your route expression changes. Take the following CLI request:

    www/index.php some command --name value --flag

The `Cli` request then initialises itself with that information and you can use it right away:
    
    $cli = new Europa\Request\Cli;
    
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

Detecting a Request
-------------------

You can return the appropriate request type for the current context.

    Europa\Request\RequestAbstract::detect();
