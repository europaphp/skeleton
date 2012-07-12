Reflection
==========

The reflection component extends on PHP's build in reflection classes and offers some very useful features such as calling methods using named parameters instead of index-based parameters and docblock / doctag parsing.

All reflectors implement the `Reflectable` interface.

For the examples, we will use the following class where it applies:

    <?php

    class MyClass extends MyClassAbstract implements MyInterface
    {
        use MyTrait;
        
        /**
         * Some property.
         * 
         * @var string
         */
        private $myProperty;
        
        public function __construct($name, $value)
        {
        
        }
        
        public function getValue($name)
        {
        
        }
        
        /**
         * Returns both values separated by a colon.
         * 
         * @param string $param1 The first parameter.
         * @param string $param2 The second parameter.
         * 
         * @return string
         */
        public function myMethod($param1, $param2)
        {
            return $param1 . ': ' . $param2;
        }
    }

DocBlock
--------

The `DocBlock` component is a very large part of the Reflection component. It allows you to represent a doc block as an object. You can parse an existing doc block and modify it or create one using the API.

    <?php

    use Europa\Reflection\DocBlock;

    $block = new DocBlock('
        /**
         * The doc block description.
         * 
         * @param string $name  The name parameter.
         * @param string $value The value parameter.
         * 
         * @throws InvalidArgumentException If you pass a bad argument.
         * 
         * @return string
         */
    ');

    // returns "The doc block description."
    $block->getDescription();

    // true
    $block->hasTag('param');

    // returns array of DocTag instances
    $block->getTags('param');

    // throws an exception because @see doesn't exist
    $block->getTag('see');

    // returns an empty array
    $block->getTags('see');

    $block->setDescription('New description.');
    $block->addTag(new DocTag\GenericTag('test', 'Tag value.'));

    // returns:
    // /**
    //  * New description. 
    //  * 
    //  * @param string $name The name parameter.
    //  * @param string $value The value parameter.
    //  * @throws InvalidArgumentException If you pass a bad argument.
    //  * @returns string
    //  * @test Tag value.
    //  */
    $block->compile();

    // true
    $block->compile() === (string) $block;

Currently, there is no formatting option on the doc bock when compiling.

There are special doc tags and a generic tag used for tags where a class is not defined. The available doc tag classes are:

- `AuthorTag`
- `FilterTag`
- `GenericTag`
- `ParamTag`
- `ReturnTag`
- `ThrowsTag`

All tags extend the `GenericTag` and have access to the following methods:

- `string` `__toString()` Alias for `compile()`.
- `string` `compile()` Compiles the tag.
- `string` `compileValue()` Compiles the tag value. This is overridden to provide tag specific compilation.
- `GenericTag` `parse($tag)` Parses the specified tag value.
- `void` `parseValue($value)` Parses the specified value. This is overridden to provide tag specific parsing.
- `string` `tag()` Returns the tag name not including the "@".
- `string` `value()` Returns the value of the tag. This is anything after the tag name.

Each tag may have methods specific to that tag.

### AuthorTag

- `AuthorTag` `setName(string $name)`
- `string` `getName()`
- `AuthorTag` `setEmail(string $email)`
- `string` `getEmail()`

### FilterTag

- `getInstance()` Returns an instance of the class specified in the filter tag.

### ParamTag

- `ParamTag` `setType(string $type)`
- `string` `getType()`
- `ParamTag` `setName(string $name)`
- `string` `getName()`
- `ParamTag` `setDescription(string $description)`
- `string``getDescription()`

### ReturnTag

- `ReturnTag` `setTypes(array | string $type)`
- `array` `getTypes()`
- `ReturnTag` `setDescription(string $description)`
- `string` `getDescription()`
- `bool` `isValid(mixed $value)`

ClassReflector
--------------

The `ClassReflector` extends PHP's `ReflectionClass` and enables you to use named arguments, doc block parsing, trait checking and more.

Type checking works for classes, abstract classes, traits and interfaces:

    <?php
    
    $class = new ClassReflector('MyClass');
    
    // true
    $class->is('MyTrait');
    
    // true
    $class->isAny(['SomeOtherClass', 'MyInterface']);
    
    // false
    $class->isAll(['SomeOtherClass', 'MyIterface']);

Retrieving methods return a `MethodReflector` or an array of `MethodReflector` instances:

    // instanceof MethodReflector
    $class->getMethod();
    
    // array of MethodReflector instances
    $class->getMethods();

Getting a doc block will return the classes doc block. If no doc block exists for the current class, it goes up through the inheritance tree. If a doc block is still not found, it goes through each interface until it finds one. If it still can't find one, it retursn `null`.

    // instanceof DocBlock
    $class->getDocBlock();

Getting a doc comment behaves the same as a doc block, but instead it returns it as a string:

    // returns the doc block string
    $class->getDocComment();

If you prefer to call your constructor using named parameters instead of an indexed array, you can:

    // instanceof MyClass
    $class->newInstanceArgs([
        'name'  => 'my name value',
        'value' => 'my value value'
    ]);

Using the overridden `newInstanceArgs()` method also allows you to pass no arguments. The default behavior is raise an error which doesn't make sense if there is a default value of `null` allowed by the default PHP implementation.

MethodReflector
---------------

The `MethodReflector` class extends on the built in `ReflectionMethod` class. It offers functionality that otherwise is not available.

Using the class from our first example, we can derive quite a bit of information:

    <?php
    
    use Europa\Reflection\MethodReflector;
    
    $method = new MethodReflector('MyClass', 'myMethod');
    
    // instanceof ClassReflector
    $method->getClass();
    
    // false
    $method->isInherited();
    
    // "public"
    $method->getVisibility();
    
    // ['value1', 'value2']
    $method->mergeNamedArgs([
        'param2' => 'value2',
        'param1' => 'value1'
    ]);
    
    // "value1: value2"
    $method->invokeArgs([
        'param2' => 'value2',
        'param1' => 'value1'
    ]);
    
    // "string"
    $method->getDocBlock()->getTags('param')[0]->getType();

Using `getDocBlock()` or `getDocComment()` on the `MethodReflector`, like in the `ClassReflector`, will go up the inheritance tree as well as check traits and interfaces.

PropertyReflector
-----------------

The `PropertyReflector` gives you visibility and doc block functionality.

    <?php
    
    $property = new PropertyReflector('MyClass', 'myProperty');
    
    // "private"
    $property->getVisibility();
    
    // true
    $property->getDocBlock()->hasTag('var');