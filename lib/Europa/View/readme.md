View
====

Apart from controllers, the view is the other main component in EuropaPHP. It is designed to give you a common API to render data to any format by implementing `ViewInterface`.

The available views are:

- Json
- Php
- Xml

Unlink some other frameworks, views are specifically designed to be kept away from your controllers. Say for example we get a response from a controller:

    <?php
    
    use Controller;
    use Europa\Fs\Locator;
    use Europa\Request\Http;
    use Europa\View\Json;
    use Europa\View\Php;
    use Europa\View\Xml;
    
    // mock a request
    $request = new Http;
    $request->setParam('id', 1);
    
    // action the controller and get the response
    $context = (new User($request))->action();

Now that you have a generic response, you can use a view to render that. Say the client wants JSON:
    
    // { ... }
    (new Json)->render($context);

You may want to take the response and make it available to clients wanting XML:

    // <xml ...
    (new Xml)->render($context);

Or, you may have some view scripts written in PHP that you want to render the data with:

    // <!doctype ...
    (new Php)->setScript('/path/path/to/my/script.php')->render($context);

The decoupling of the view and controller relationship helps you to write more modular and reusable code, however, it is still up to you to write that code.

PHP Views
---------

The `Php` view can be as simple as shown above to use but there are some things you can do to set up automation.

### Accessing Variables

As you have seen, when you render a view, it gets passed a context. In order to access this context, you simply access a variable using the name of the context parameter you want. For example, if we are given the following context:

    [
        'param1' => 'value1',
        'param2' => 'value2
    ]

We can access it in our views by:

    <?php echo $param1; ?>: <?php echo $param2; ?>

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

    <?php
    
    use Europa\Fs\Locator;
    use Europa\View\Php;
    
    $loc = new Locator;
    $loc->addPath('/path/to/views', 'phtml');
    
    $view = new Php;
    $view->setLocator($loc);
    $view->setScript('relative/path/to/view');
    
    // would render /path/to/views/relative/path/to/view.phtml if it exists
    // or throw an exception if it doesn't
    $view->render();

This can be extremely useful in architectures that have a plugin or module system because you can have multiple view paths and have it find the first available. All you need to do is add multiple paths:

    $loc->addPath('/path/to/plugin1/views', 'phtml');
    $loc->addPath('/path/to/plugin2/views', 'phtml');

### Using Helpers

You can also use helpers within your views. A helper is just a PHP class and that's it. It doesn't have to implement any interfaces or anything. For this reason, we use a container to setup and return instances of our helpers. If we have the following helper:

    <?php
    
    namespace Helper;
    
    class MyHelper
    {
        private $view;
        
        public function __construct(Php $view)
        {
            $this->view = $view;
        }
        
        public function script()
        {
            return $this->view->getScript();
        }
    }

We could set it up and use it like so:

    <?php
    
    use Europa\Container\Finder;
    use Europa\Filter\ClassNameFilter;
    use Europa\View\Php;
    
    $helpers = new Finder;
    $view    = new Php;
    
    // tell the view which container to use for helper resolution
    $view->setHelpers($helpers);
    
    // tell it to find the helpers in the "Helper" namespace
    $helpers->getFilter()->add(new ClassNameFilter(['prefix' => 'Helper\\']));
    
    // set up the helper
    $helpers->config('Helper\MyHelper', function() use ($view) {
        return [$view];
    });
    
    // since the helpers uses the view, we set a script on it so the helper can use it
    $view->setScript('test/script');
    
    // now we can use this useless helper if we want to
    // "test/script"
    $view->myHelper->script();
