Controller
==========

Controllers exist to return a renderable response. They should be thin. Very thin. They shouldn't directly access a data source, they should not know about how the view is rendering its response or what type of request was made to it. Their only concern is what parameters were passed in and how they are going to respond to that.

The recommended controller for web applications (which may also contain command line actions) is the `RestController`.

RESTful Controllers
-------------------

In order to create the basis for a RESTful controller, you extend the `RestController` class.

    <?php
    
    namespace Controller;
    use Europa\Controller\RestController;
    
    class Index extends RestController
    {
        public function get()
        {
            ...
        }
    }

Restful controllers support all methods of the [HTTP 1.1 specification](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html) as well as one additional method, `cli`, for making command line requests to the same controller. While not exactly RESTful, the `cli` method allows you to point to the same controller using the command line which in some cases can be very useful.

The `RestController` derives from `ControllerAbstract` which requires an instance of `RequestInterface` and uses the `getMethod()` method to discern which method to call.

If you need to specify which method to use, you specify it on the request.

    <?php
    
    use Europa\Request\Http;
    
    $request = new Http;
    $request->setMethod('get');

The controller then knows which method it needs to call when it is actioned.

    $controller = new Controller\Index($request);
    $controller->action();

Basic Controllers
-----------------

At their most basic, controllers must implement `ControllerInterface`.

    <?php
    
    namespace Controller;
    use Europa\Controller\ControllerInterface;
    use Europa\Request\RequestInterface;
    
    class Index implements ControllerInterface
    {
        public function __construct(RequestInterface $request)
        {
            
        }
        
        public function action()
        {
            
        }
    }

Custom Controllers
------------------

Generally using the interface and building a controller from scratch is not necessary and if not using a `RestController`, you would use `ControllerAbstract` to create your own type of controller.

For example, you can create multi-action controllers like in some other frameworks:

    <?php
    
    namespace Europa\Controller;
    use Europa\Controller\ControllerAbstract;
    
    abstract class MultiActionController extends ControllerAbstract
    {
        const ACTION = 'index';
        
        public function getActionMethod()
        {
            return $this->request()->getParam('action', self::ACTION) . 'Action';
        }
    }

You may ask why this is not provided. To answer that, I urge you to ask yourself the following questions:

- Why should PHP parse the whole file to only call a single action?
- If I've already got the ability to have RESTful controllers, what benefits am I gaining by not using that?
- Why would I want to facilitate logical cohesion (second worst next to coincidental) over functional cohesion (best)? See [Wikipedia](http://en.wikipedia.org/wiki/Cohesion_\(computer_science\)).

If you can come up with a valid argument, go for it.
