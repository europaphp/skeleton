Controller
==========

Controllers exist to return a renderable response. They should be thin. Very thin. They shouldn't directly access a data source, they should not know about how the view is rendering its response and they should not have know what whether it was accessed via CLI or HTTP. Their only concern should be what parameters were passed in and how they are going to respond to that.

Europa comes with a default controller abstraction called `Europa\Controller\ControllerAbstract`. It enables named arguments to be passed in from the request automatically and support for `@filter` doc tags which pass the controller sequentially to whatever filter or filters you specify.

Passing Arguments to a Controller Method
----------------------------------------

You have a method with a parameter named `$id`:
    
    public function get($id);

The `ControllerAbstract` will look in the supplied request for a parameter named `id` and map it to the method when calling it. Since PHP accepts arrays in the request, you can also specify an array:

    public function put(array $content);

Defining Filters for Your Controller
------------------------------------

A controller accepts two types of filters: Class Filters and Method Filters. Class Filters are applied to all methods in it's class and Method Filters are only applied to the method that it is bound to. Filters need only be `callable` but is easiest to put them into class form to make creating and calling them easier from doc tags.

If we take the following class:

    <?php
    
    namespace My\Controller;
    Europa\Controller\ControllerAbstract;
    
    /**
     * @filter My\Filter\Authorize
     */
    class Account extends ControllerAbstract
    {
        public function get()
        {
            
        }
    }

The `Authorize` filter is applied to all methods in the class. You could apply this to a single action, or method by assign it directly to that method instead of the class.

    <?php
    
    namespace My\Controller;
    Europa\Controller\ControllerAbstract;
    
    class Account extends ControllerAbstract
    {
        /**
         * @filter My\Filter\Authorize
         */
        public function get()
        {
            
        }
    }

Your filter may look like this:

    <?php
    
    namespace My\Filter;
    use Europa\Controller\ControllerAbstract;
    
    class Authorize
    {
        public function __invoke(ControllerAbstract $controller)
        {
            // authorize the user
        }
    }

Accessing the Request
---------------------

Within an action, you may access the request by using the `request()` method.

    $this->request->getParams();

You cannot access the request from the constructor since it has not been passed in yet.

Forwarding Requests to Other Actions
------------------------------------

Inside of an action, you can forward requests to other actions by using the `forward()` method.

    $this->forward('myOtherAction');

Forwarding will only work for actions within the same controller. It does not allow you to forward to another controller by design.

Conventions
-----------

The default controller assumes a few things for you.

1. The parameter in the request that defines which action should be called is `action`.
2. The doc tag used for filters is `@filter`. You can use as many filters as you want.
3. Filters are invoked after construction, but before invokation.
4. Request parameters can automatically be passed in as action parameters by name just by defining them in your action method definition.
5. You can write your own controllers as long as they are `callable`. The request is always passed in as the only argument.
6. You may define a constructor if you like to set up the controller since the abstract class does not define one.