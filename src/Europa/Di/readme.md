Dependency Injection
====================

DI stands for [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection). If you are not familiar with this, it is the act of injecting one dependency object into a dependent object.

Dependency Injection Containers - which facilitate dependency injection and alleviate its painful setup - stands at the core of Europa. Since everything boils down to pre-configured objects interacting with one another, this was a no-brainer.

Service Containers
------------------

A service container is an object store that manages different configurations of objects and whether or not that object setup should be cached and reused (singleton) or re-created every time you access it (transient).

### Separating Containers

The service container, in addition to managing object configurations, allows you to have separate named service container instances that may contain different object configurations.

    use Europa\Di\Container;

    $container1 = new Europa\Di\Container
    $container2 = Europa\Di\Container::create();
    $container3 = Europa\Di\Container::create('my.container');

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
        
        public function mongo($config)
        {
            return new Mongo($config->mongo->dsn);
        }
        
        public function mongodb($config, $mongo)
        {
            return $mongo->selectDatabase($config->mongo->db);
        }
    }

The above class illustrates how you may design a configuration that provides a configuration object, a mongo server connection object and a mongo database object.

You could then use this configuration and apply it to a container.

    $container = Europa\Di\ServiceContainer::create('my.container')->configure(new My\Di\Configuration);

Using these objects is as simple as accessing them like properties.

    $results = Europa\Di\ServiceContainer::create('my.container')->mongodb->myCollection->find([ … ]);

What's nice about using this configuration is that objects are configured on an on-demand basis. This means that the code within the configuration methods aren't executed until the service is accessed for the first time.

### Container Usage

As mentioned before, services within a container can be accessed via normal object sytnax.

    if ($container->has('my.service')) {
        $container->get('my.service')->doSomething();
    }

If you attempt to access a service that doesn not exist, it will throw an exception indicating why. It will also tell you if it finds a service of the same name in another container.

You can also register and remove services that aren't in your configurations.

    $container->set('my.service', function() {
        return new SomeService;
    });
    
    $container->remove('my.service');

Any mutations that happen to the container after it is configured are your responsibility to manage.
