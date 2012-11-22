Response
========

The `Response` component is similar to the `Request` component in that it implements a common interface between HTTP and CLI.

HTTP Responses
--------------

The `Europa\Response\Http` class allows you to set headers without affecting header output, and output a response. Only when `send()` is called, are headers sent then the response output.

    $http = new Europa\Response\Http;
    
    // set content type
    $http->contentType = Europa\Request\HttpInterface::JSON
    
    // outputs the headers then the json response
    $http->setBody('{ success: true }')->send();

The `Europa\App\App` class uses this in conjunction with a `Europa\View\ViewInterface`:

    $http = new Europa\Response\Http;
    $view = new Europa\View\Json;
    $json = ['success' => true];
    
    // "{ success: 'true' }"
    $http->setHeader('Content-Type', 'application/json');
    $http->setBody($view->render($json));
    $http->send();

If an error occurred, then you can also set an appropriate response code.

    $http->setStatus(404);

CLI Responses
-------------

Command line responses are super simple. They simply output the response. They exist to solely to give CLI a way to respond in the same fashion as the HTTP response.

    <?php
    
    use Europa\Response\Cli;
    
    // "my response..."
    (new Cli)->setBody('my response...')->send();

Detecting a Response
--------------------

You can return the appropriate response type for the current context.

    Europa\Response\ResponseAbstract::detect();
