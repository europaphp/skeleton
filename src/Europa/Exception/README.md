Exceptions
==========

The exception component is a small way of automating exception throwing, code generation and message formatting.

    Europa\Exception\Exception::toss('My name is "%s".', 'Chuck Norris');

An exception code would automatically be generated from the exception class and the message would be formatted.

The only drawback to this is that it adds another call to the call stack when it is displayed. Although opinion, this is very minor.

### Extending

Exceptions were also designed to be extended.

    <?php
    
    namespace My\Exception;
    use Europa\Exception\Exception;
    
    class SomeDescriptiveError extends Exception
    {
        
    }

And you don't have to do anything, just define the class and an error code is automatcially generated for you.

    My\Exception\SomeDescriptiveException::toss('Some crazy stuff happened.');
