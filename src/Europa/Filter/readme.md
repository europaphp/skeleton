Filter
======

The filter component is used by other parts of the framework but can also be used on its own. There are many filters in here that you can use, or you can define your own that can be used anywhere in the framework as long as it is `callable`.

Usage
-----

All filters are `callable`:
    
    $filter = new Europa\Filter\CamelCaseSplitFilter;
    
    // ['Camel', 'Case']
    $filter('CamelCase');

List of Filters
---------------

Below is a complete list of filters and what they do.

### CamelCaseSplitFilter

For splitting a string by each capital letter.

### ChainFilter

For chaining together multiple filters. The passed in value is modified by each filter then returned.

### ClassNameFilter

Turns a string into a class name.

### ClassResolutionFilter

Extends `FilterArrayAbstract` and allows you to use multiple filters to resolve a class. Each filter is applied to the original value until it finds a class that exists.

### FilterArrayAbstract

Exists as a base class for authoring filters that can accept child filters for chaining.

### FromStringFilter

Converts the value from a string to a scalar data type.

### LowerCamelCaseFilter

Turns any string into a lower-camel-cased string such as a method name or property name.

### MapFilter

Accepts an mapping array. If the incoming value matches a key, the value is returned.

### MethodNameFilter

Returns a class name from the specified value.

### ToStringFilter

Will turn any type of value into a string.

### UpperCamelCaseFilter

Will turn any string into an upper-camel-cased string. Good for class names without namespace separators.

### UrlFilter

Will turn any string into a string that can be placed into a URL.
