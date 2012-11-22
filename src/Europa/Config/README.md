Config
======

The configuration component exists to make manipulating configuration arrays easier and more fluid. Many parts of Europa use this component to handle configuration arrays internally.

General Usage
-------------

The most common usage is importing from several sources in order to merge default configuration with custom configuration.
    
    $defaults = [
        'value1' => '',
        'value2' => 'second value'
    ];
    
    $custom = [
        'value1' => 'first value'
    ];
    
    $config = new Europa\Config\Config($defaults, $custom);
    
    // first value
    echo $config->value1;
    
    // second value
    echo $config->value2;

The config also handles multi-dimensional arrays.
    
    $config = new Europa\Config\Config([
        'first' => [
            'second' => 'dimension'
        ]
    ]);
    
    // dimension
    echo $config->first->second;

Dot-notation
------------

You can use dot-notation in your option names and it will automatically nest those values for you:

    $config['my.sub.key'] = 'value';
    
    // value
    echo $config['my']['sub']['key'];

This can make deep nested options more readable by only having to pass in an array with a single dimension.

Referencing Other Values
------------------------

You can reference other configuration options from within the values of another option. In order to notify the parser that you wish to evaluate it, you prefix your value with `=`.

    $config = new Europa\Config\Config([
        'root' => '/my/root/path',
        'app'  => '={root}/my/app/path'
    ]);
    
    // /my/root/path/my/app/path
    echo $config->app;

If you want to use a `=` as the first character, but don't want the value evaluated, then just escape it.

    'equals' => '\='

Referencing Values from a Parent
--------------------------------

Each configuration object contains a special property called `parent`. When this is accessed, it returns the parent config object if it has one. This is useful when you have sub-options that need to reference parent options.

    $config = new Europa\Config\Config([
        'root'   => '/my/root/path',
        'modules => [
            'my-module' => [
                'src' => '={parent.parent.root}/my-module/src'
            ]
        ]
    ]);
    
    // /my/root/path/my-module/src
    echo $config->modules['my-module']->src;

Array-like Behavior
-------------------

`ArrayAccess`, `Countable` and `IteratorAggregate` are also implemented.

    <?php
    
    $config = new Europa\Config\Config([
        'cookies' => [
            'chocchip',
            'pummpkin'
        ]
    ]);
    
    // 2
    echo count($config['cookies']);
    
    // 1. chocchip
    // 2. pumpkin
    foreach ($config['cookies'] as $index => $name) {
        echo ($index + 1) . '. ' . $name;
    }

Importing and Exporting
-----------------------

Once a config is estabilished, you can `import` from any traversable item as well as export to a raw `array`.

    <?php
    
    $config = new Europa\Config\Config;
    
    $config->import([
        'some' => [
            'array' => [1, 2, 3]
        ]
    ]);
    
    // 'some' => [
    //     'array' => [1, 2, 3]
    // ];
    print_r($config->export());

Making Readonly
---------------

If you want to prevent further changes to the configuration, you can mark the object as readonly. This can be done for any level of configuration as well.

    <?php
    
    $config = new Europa\Config\Config([
        'level1' => [
            'level2' => [
                'some' => 'value'
            ]
        ]
    ]);
    
    $config->readonly();

You can make it writable again if need be:

    $config->readonly(false);

If you want the top level to be writable, but not another level you just call `readonly()` on the level you want to make readonly.

    $config->level1->level2->readonly();

However, that means that you can still modify level1 and overwrite level2.

Aggregating Keys and Values
---------------------------

There are also a couple of methods for aggregating keys and values. Calling `keys()` on a particular level will return the key names for that level as an array. Calling `values()` does the same for the values at that level, including sublevels.

Reading Configuration from Files
--------------------------------

The configuration component ships with three adapters for reading from files and all are `callable`.

1. Ini
2. Json
3. Php

They can all be used the same way and just require a path passed to the constructor. Additionally, the `Config` constructor and `import()` methods accept a string, callable as well as the already described traversable argument. If a string is given, it is assumed to be a path to a config file and the suffix is used to determine which adapter to use.

Each adapter will check for file existence and if it does not exist, emit an exception inindicating as much.

### Getting Values into your Config Object

Configuration objects allow you to use a string. The path suffix determines the type of adapter used.

    $ini = new Europa\Config\Config('config.ini');

Is equivalent to:

    $ini = new Europa\Config\Config(new Europa\Config\Adapter\Ini('config.ini'));

And similar to:

    $ini = new Europa\Config\Config(function() {
        return parse_ini_file('config.ini');
    });

Aaaand:

    $ini = new Europa\Config\Config(parse_ini_file('config.ini'));

### Ini

INI sections are processed and since dot-notation is supported, so you can rely on your options with dot-notation to create nested objects.
    
    db.host = localhost
    db.name = mydb
    db.user = username
    db.pass = password

The values would be accessible using sub-levels.
    
    $ini = new Europa\Config\Config('config.ini');
    
    // localhost
    echo $ini->db->host;

### Json

JSON files are just a text file that contains a JSON object in it.

    {
        "db": {
            "host": "localhost",
            "name": "mydb",
            "user": "username",
            "pass": "password"
        }
    }

Parsing and accessing is the same as with INI files.

    $json = new Europa\Config\Config('config.json');
    
    // localhost
    echo $json->db->host;

### Php

PHP files are files containing PHP code that returns an associative array of options and is the most efficient form to load configuration from, although, maybe less convenient.

    <?php
    
    return [
        'db' => [
            'host' => 'localhost',
            'name' => 'mydb',
            'user' => 'username',
            'pass' => 'password'
        ]
    ];

    $php = new Europa\Config\Config('config.php');
    
    // localhost
    echo $php->db->host;

### Why No YAML?

It's too hipster.