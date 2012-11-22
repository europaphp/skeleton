Dependency Injection
====================

DI stands for [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection). It is also known as the [Service Locator Pattern](http://en.wikipedia.org/wiki/Service_locator_pattern) or [Inversion of Control](http://en.wikipedia.org/wiki/Inversion_of_control). If you are not familiar with this, it is the act of injecting one dependency object into a dependent object.

Service Containers - which facilitate dependnecy injection and alleviate its painful usage - is the core of Europa. Since everything boils down to pre-configured objects interacting with one another, this was a no-brainer.

Service Containers
------------------

A service container is an object store that manages different configurations of objects and whether or not that object setup should be cached and reused (singleton) or re-created every time you access it (transient).

### Separating Containers

The service container, in addition to managing object configurations, allows you to have separate named service container instances that may contain different object configurations.

    use Europa\Di\ServiceContainer;

    $container1 = ServiceContainer::myFirstContainer();
    $container2 = ServiceContainer::two();

By calling the container statically, it automatically creates an instance of itself using the name of that you used and caches it so that next time you call it by that name, it returns the one it previously set up.

When separate containers are created, their objects are maintained separate from each other, even if they are configured the same way. This could essentially allow you to have one application configuration and two separate instances of it existing at one time. Practically speaking, you can use this to your advantage for unit testing purposes and other things.

### Container Configurations

The most useful aspect of Europa's container system is the ability to maintain configurations. Configurations are applied using the `configure()` method on a service container. The preferred way to apply a configuration is by extending the `Europa\Di\ConfigurationAbstract` class and passing a new instance of that to `configure()`.

    <?php
    
    namespace My\Di;
    use Europa\Config\Config;
    use Europa\Di\ConfigurationAbstract;
    use Mongo;
    
    class Configuration extends ConfigurationAbstract
    {
        public function config()
        {
            return new Config('/path/to/config.json');
        }
        
        public function mongo()
        {
            return new Mongo($this->config->mongo->dsn);
        }
        
        public function mongodb()
        {
            return $this->mongo->selectDatabase($this->config->mongo->db);
        }
    }

The above class illustrates how you may design a configuration that provides a configuration object, a mongo server connection object and a mongo database object.

You could then use this configuration and apply it to a container.

    $container = Europa\Di\ServiceContainer::main()->configure(new My\Di\Configuration);

Using these objects is as simple as accessing them like properties.

    $results = $container->mongodb->myCollection->find([ … ]);

What's nice about using this configuration is that objects are configured on an on-demand basis. This means that the code within the configuration methods aren't executed until the service is accessed for the first time saving you unnecessary CPU cycles from objects that may not be used on a given request.

Since configurations are `callable` we can use a closure if we want:
    
    $configuration = function($container) {
        $container->myService = new My\Service;
    };

    $container1->configure($configuration);
    $container2->configure($configuration);

### Container Usage

As mentioned before, services within a container can be accessed via normal object sytnax.

    if (isset($container->myService)) {
        $container->myService->doSomething();
    }

If you attempt to access a service that doesn not exist, it will throw an exception indicating why. It will also tell you if it finds a service of the same name in another container.

You can also register and remove services that aren't in your configurations.

    $container->myService = new SomeService;
    
    unset($container->myService);

Beware, services that are registerd using a configuration will be removed if they are `unset` since they are simply registered as normal.

### Passing Arguments to Configurations

Something that may be of use - and that is also used internally in Europa - is the ability to pass parameters into a configuration method. For example, the `Europa\App\AppConfiguration` configuration requires both the default configuration and the custom configuration that was passed into the constructor for `Europa\App\App`.

We may have a configuration method like so:

    public function myService(array $config);

And we can pass arguments to it:

    $configuration->setArguments('myService', $config);

If we want to pass in arguments using an array:

    $configuration->setArgumentsArray('myService', [$config]);

### Marking a Service as Transient

There are two ways to mark a service as transient. The easiest way if you are using configurations is to annotate the method using a `@transient` doc tag.

    /**
     * Returns my service.
     * 
     * @transient
     * 
     * @return My\Service
     */
    public function myService();

Since doc tags cascade all the way back to interfaces, you can define this on an interface that a configuration implements.

The second way is to do it on the container itself:

    $container->transient('myService');

### Using Interfaces with Configurations

If may be useful to require that a configuration implement certain methods, thus ensuring that whatever uses that configuration will have access to the defined services. This is as simple as implementing an interface in your configuration class.

    <?php
    
    namespace My\Di;
    
    interface ConfigurationInterface
    {
        /**
         * Returns my service.
         * 
         * @transient
         * 
         * @return My\Service
         */
        public function myService();
    }

And implement it:

    <?php
    
    namespace My\Di;
    use My\Service;
    
    class Configuration implements ConfigurationInterface
    {
        public function myService()
        {
            return new Service;
        }
    }

The only problem with this is that once the configuration is applied, you don't know if that container will necessarily have the same definition as the interface. We can check for this:

    $container->configure(new My\Di\Configuration);
    
    // true
    $container->provides('My\Di\ConfigurationInterface');

And we can even tell it to throw an exception if it does not provide the specified configuration or interface:

    $container->mustProvide('Some\Other\Configuration');

One thing to note is that you can pass either a class or interface class name as the argument to `provides()` or `mustProvide()`.

### Interoperability

Containers can also be interchangeable with `callable` items depending on the level of functionality you require. When you invoke a container it calls `__get()` internally and passes in the argument you pass to it.

    $container('myService');
