Controller
==========

Controllers exist to return a renderable response. They should be thin. Very thin. They shouldn't directly access a data source, they should not know about how the view is rendering its response and they should not know what type of request was made to it. Their only concern is what parameters were passed in and how they are going to respond to that.

The recommended controller for web applications (which may also contain command line actions) is the `RestController`.

RESTful Controllers
-------------------

A rest controller allows you to specify methods which correspond to methods from the [HTTP 1.1. Method Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html). The allowed methods are:

- cli
- connect
- delete
- get
- head
- options
- patch
- post
- put
- trace
- all, catches all requests

The `cli` methods is used for catching all command line requests and `all` is used for catching any request where a corresponding method is not defined. All other methods directly correspond to the methods in the HTTP 1.1 Spec.

A controller may look like the following:

    <?php
    
    namespace Controller;
    use Europa\Controller\RestController;
    
    class Content extends RestController
    {
        public function get()
        {
            
        }
        
        public function put()
        {
            
        }
    }

Passing Arguments to a Controller Method
----------------------------------------

Any controller that derives from `ControllerAbstract` (includes the `RestController`) allows named parameters to be specified in each method definition.

For example, if you have a method with a parameter named `$id`:
    
    public function get($id);

The `ControllerAbstract` will look in the supplied request for a parameter named `id` and map it to the method when calling it. Since PHP accepts arrays in the request, you can also specify an array:

    public function put(array $content);

Custom Controllers
------------------

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

Generally using `ControllerInterface` and building a controller from scratch is not necessary and if not using a `RestController`, you would use `ControllerAbstract` to create your own type of controller.

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
