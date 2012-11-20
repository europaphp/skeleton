App
===

The application layer serves as a component responsible for eliminating unnecessary boilerplate code by bringing all of the other components together into one. It is designed to break apart your application into smaller chunks of code, or modules. In order for your application to run, you need at least one module.

To add modules to your application, you would access the `modules` dependency and append items to it. At its most basic, all you need to specify is the module directory name:

    <?php

    $app = new Europa\App\App;
    $app->getServiceContainer()->modules[] = 'main';

To run your application, all you need to do is invoke the app instance:

    $app();

Modules
-------

Modules are chunks of application code that are organised into their own directories. The default module structure is defined as follows:

* module-name
** configs
*** config.json
*** routes.json
** src
*** SomeNamespace
**** SomeClass.php
*** Controller
**** SomeController.php
** views
*** controller
**** action.php

Every part of this structure can be customised by providing the module with a custom configuration:

    <?php

    $app->getServiceContainer()->modules[] = new Europa\App\Module('module-name', [
        'bootstrapperNs' => 'Bootstrapper',
        'config'         => 'configs/config.json',
        'routes'         => 'configs/routes.json',
        'src'            => ['src'],
        'views'          => ['views']
    ]);

### Bootstrapping

Modules are bootstrapped using a bootstrapper class. By default, the bootstrapper is contained under the `Bootstrapper` namespace and the class name is the same name as your module, but camel-cased. This means the bootstrapper for `module-name` would be `Bootstrapper\ModuleName`.

### Configuration

Module configuration is taken from the `configs/config.json` file. This can be changed by updating the `config` configuration option.

### Routes

Routes configuration is taken from the `configs/routes.json` file. This can be changed by updating the `routes` configuration option.

### Autoload Paths

Autoload paths are taken from the `src` configuration option. This is an array of paths relative to the module install path. This defaults to `src`.

### View Paths

By defualt views are loaded relative to the `views` path. This can be altered by changing the `views` configuration option. If you are not using views that are loaded from script files, then you may not even use this option.

### Requiring Other Modules

Sometimes one module's behavior relies on another module. If this is the case, you can tell one module to require another module. During bootstrapping, the module manager will attempt to bootstrap all dependencies before the dependant. If a required module does not exist, an exception is raised.

    <?php

    $modules   = new Europa\App\Modules;
    $modules[] = 'some-module';
    $modules[] = 'some-other-module';

    $modules['some-module']->requires('some-other-module');

    $modules->bootstrap();

Customizing Application Behavior
--------------------------------

The default setup will be good for most people, however, Europa was originally designed for the person who likes to tinker with things. You can modify parts of the application runner by passing in configuration options to the constructor:

    $app = new Europa\App\App([
        'paths.root'   => '..',
        'paths.app'    => '={root}/app',
        'view.default' => 'Europa\View\Php',
        'view.script'  => ':controller/:action',
        'view.suffix'  => 'php'
    ]);

Due to the nature of how the `Config` component works, you could even specify a file:

    $app = new Europa\App\App('/path/to/config/file.ini');

Or closure:

    $app = new Europa\App\App(function() {
        return [ ... ];
    });

Or custom configuration class that either defines public properties or is traversable:

    $app = new Europa\App\App(new My\Config);

If you need to access the configuration, you can do so by getting it from the service container just like we did with the modules:

    // Returns the application path.
    $app->getServiceContainer()->config->paths->app;

Application Configuration Options
---------------------------------

`paths.root`

The installation path of your application. The application will attempt to auto-detect this by default by using `..`, but this means that whatever is using this value must be in a sub-directory of the installation root.

`paths.app`

The path to the application folder where the modules are kept. This defaults to `/your/install/path/app`.

`view.default`

The default view class to use. This defaults to using `Europa\View\Php`, but this may not even be necessary.

`view.script`

If using a view that implements `Europa\View\ViewScriptInterface`, then the `->setScript()` method will be passed this value. You can substitute request parameters by prefixing the request parameter name with a colon. For example, the default value is `:controller/:action`.

`view.suffix`

If using a view that implements `Europa\View\ViewScriptInterface`, then the view suffix will be set to this value. The default suffix is `php`.