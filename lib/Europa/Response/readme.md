Response
========

The `Response` component is similar to the `Request` component in that it implements a common interface between `Http` and `Cli`.

HTTP Responses
--------------

The `Http` response allows you to set headers without affecting header output, and output a response. Only when `output()` is called, are headers sent then the response output.

    <?php
    
    use Europa\Response\Http;
    
    $http = new Http;
    
    // set content type
    $http->contentType = Http::JSON;
    
    // outputs the headers then the json response
    $http->output('{ success: true }');

The `Europa\App\App` class uses this in conjunction with a `Europa\View\ViewInterface`:

    <?php
    
    use Europa\Response\Http;
    use Europa\View\Json;
    
    $http = new Http;
    $view = new Json;
    $json = ['success' => true];
    
    // "{ success: 'true' }"
    $http->setHeader('Content-Type', 'application/json')->output($view->render($json));

CLI Responses
-------------

Command line responses are super simple. They simply output the response. They exist to solely to give CLI a way to respond in the same fashion as the HTTP response.

    <?php
    
    use Europa\Response\Cli;
    
    // "my response..."
    (new Cli)->output('my response...');
