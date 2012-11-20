Europa
======

These are the Europa framework files.

When you include `Europa\Fs\Loader`, this directory is automatically registered as an autoload path. Any classes in here following the PHP standard naming convention will be autoloadable. This includes classes with both PHP 5.2 and 5.3 style of namespace separators (both `_` and `\`).

If you are using Europa as a composer package and are using the `autoload.php` file already, you don't have to do anything.

What's New
----------

There's a lot of new stuff. For a more in depth look, check out each component's readme.

### SRP Design

EuropaPHP 2's major difference to previous releases is it's focus on how the SRP is applied specifically in a PHP context. As a result of this design, a lot of class functionality can be exposed through a single method. When this was the case, in order to maximise flexibility and eliminate unnecessary complexity, the class would expose that functionality using `__invoke()`. This makes the class itself interchangeable with anything that `is_callable()`. By utilising PHP 5.4, type-hints have been placed where appropriate to use `callable` so that you can use a callable class or closure to do what you need.

### Service Containers

Another major difference is how service containers are configured. Service containers take configurations via a `configure()` method. The configuration can be anything that is `callable`, however, there is a `Europa\Di\ConfigurationAbstract` class that you can extend to organise your configurations into classes. A bug benefit to using these classes is that you can define interfaces for these configurations and then check the container if they provide that configuration or configuration interface.

### Controllers

Controllers can now simply be closures, or anything that is `callable`. This way, you can have very lightweight controllers that don't require any other fucntionality, or you can extend the `Europa\Controller\ControllerAbstract` class to give you access to named arguments and filters.

### Configuration

An emphasis has also been placed on passing configurations into class constructors where appropriate. This is the case for routes as well as certain views and the main application component. In doing this, a `Europa\Config\Config` class was created to make using configurations easier. The configuration component ships with a few adapters to solve most of your needs: `Europa\Config\Adapter\Ini`, `Europa\Config\Adapter\Json` and `Europa\Config\Adapter\Php`. The config class allows for dot-notation to be used for option names as well as referencing other opiton values within another option value.

### Event Management

An `Event` component now exists to allow your application to create hooks at any point in it's lifecycle. As with many other parts, events can be anything that is `callable`.

### Reflection

The reflection component now contains a `FunctionReflector` for reflecting closures as well as normal functions.

### Easier Routing

The router has been completely overhauled to make it easier to define your application's structure. Since routes are passed a configuration we can use the `Config` component to read route configurations and directly pass it on to the route. As a result, all of the types of configuration files that are supported by the `Config` component are available to the router as well.

### Effortless Content Negotiation

The `View` component now ships with a `Negotiator` that will return - based on the request that is passed in - a certain view class that it find appropriate to handle the request. The negotiator is configurable to a point and is `callable`, so substituting your own is very easy.

### Application Abstraction

The `App` component was added to provide a way of eliminating as much boilerplate code as possible while still giving you as much flexibility as possible. It takes a single service container that it uses to grab it's dependencies from. This service container must provide `Europa\App\AppConfigurationInterface` or be configured with `Europa\App\AppConfiguration`. This means that you can substitute dependencies into it's container if need be to alter it in any way shape or form. It comes with good defaults so you probably won't have to do anything. Additionally, it accepts configuration options in its constructor to control smaller things like paths.