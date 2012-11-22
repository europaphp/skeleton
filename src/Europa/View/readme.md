View
====

Apart from controllers, the view is the other main component everything revolves around in EuropaPHP. It is designed to give you a common API to render data to any format by implementing `ViewInterface`.

The available views are:

- Json
- Jsonp
- Php
- Xml

Unlike some other frameworks, views are specifically designed to be kept away from your controllers. Your view takes a context and renders that contenxt. Generally, a context is returned from a controller and handed off to the view to render, but you can use views as a separate component if you want to.

PHP Views
---------

The `Europa\View\Php` class has extra functionality for executing view logic from within the view rather than relying on the controller to tell the view what to do. This includes things like accessing 

### Accessing Variables

As you have seen, when you render a PHP view, it gets passed a context. In order to access this context, you simply access a variable using the name of the context parameter you want. For example, if we are given the following context:

    [
        'param1' => 'value1',
        'param2' => 'value2
    ]

We can access it in our views by:

    <?php echo $this->context('param1'); ?>: <?php echo $this->context('param2'); ?>

### Extending Views

You can extend other views in order to wrap a given view in another view (i.e. layouts). For example, if we had a view called `child/view` and we wanted to render it within `parent/view`:

`child/view`

    <?php $this->extend('parent/view'); ?>

`parent/view`

    <?php echo $this->renderChild(); ?>

By telling the view which script you want to extend from within the child, all view logic is kept where it belongs: inside the view. This gives you much greater control all while still making layouts easy. You can even chain together multiple layouts if you like:

    main/layout > special/layout > child/script

Since the parent view only knows to "render my child here" it's up to the child which view it wants to be rendered inside of, if at all, and allows you to extend as deep as you want.

### Rendering Partial Views

On top of extending, you can also render one view inside of another by giving the parent control. From within a script:

    <?php $this->renderScript('some/other/script', [
        'param1' => $someParmeter,
        'param2' => 'someOtherParamter'
    ]); ?>

### Using a Locator for Relative Paths

You may not want to specify the full path to your view scripts all the time or specify a suffix. To do this we'd use a `Europa\Fs\LocatorInterface`:

    $loc = new Europa\Fs\Locator;
    $loc->addPath('/path/to/views', 'phtml');
    
    $view = new Europa\View\Php;
    $view->setLocator($loc);
    $view->setScript('relative/path/to/view');
    
    // would render /path/to/views/relative/path/to/view.phtml if it exists
    // or throw an exception if it doesn't
    $view->render();

This can be extremely useful in architectures that have a plugin or module system because you can have multiple view paths and have it find the first available. All you need to do is add multiple paths:

    $loc->addPath('/path/to/plugin1/views', 'phtml');
    $loc->addPath('/path/to/plugin2/views', 'phtml');

### Using Helpers

You can also use helpers within your views. A helper is just a PHP class and that's it. It doesn't have to implement any interfaces or anything.

Accessing a helper is simple:

    <?php echo $this->myHelper; ?>

Accessing the helper using `__get()` will cache an instance (just like when using `get()` on the container). If you want to use a fresh instance, use `__call()`:

    <?php echo $this->myHelper(); ?>

To setup and return instances of our helpers we supply the view with a container. Given the following helper class:

    <?php
    
    namespace My\Helper;

    class MyHelper
    {
        private $view;
        
        public function __construct(Php $view)
        {
            $this->view = $view;
        }
        
        public function __toString()
        {
            return $this->script();
        }
        
        public function script()
        {
            return $this->view->getScript();
        }
    }

We could set it up and use it with a container like so:

    // Use a service container for hanlding helper instances.
    $helpers = new Europa\Di\ServiceContainer;
    
    // Register your helper
    $helpers->myHelper = new Europa\Di\MyHelper;
    
    // Bind it to the view.
    $phpView->setServiceContainer($helpers);

This prevents us from using any built-in helpers, though. We can alleviate this by creating a configuration for our helpers and apply both configurations to the view's service container.

    <?php
    
    namespace My\Di\Configuration;
    use Europa\Di\ConfigurationAbstract;
    use My\Helper\MyHelper;
    
    class Helpers extends ConfigurationAbstract
    {
        public function myHelper()
        {
            return new MyHelper;
        }
    }

Now that configuration is accessible to us.

    // Helper container.
    $helpers = new Europa\Di\ServiceContainer;
    
    // Default configuration.
    $helpers->configure(new Europa\View\HelperConfiguration);
    
    // Custom configuration.
    $helpers->configure(new My\Di\Configuration\Helpers);
    
    // Apply it to the view.
    $phpView->setServiceContainer($helpers);
