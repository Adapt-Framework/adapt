# Adapt Design

Before developing with Adapt it's helpful to understand how Adapt is designed and what the design enables you to do.  Adapt is flexible and almost everything can be modified or replaced.  It's possible to use Adapt to bootstrap more complex applications because, by design, Adapt can be replaced entirely.

## The foundations
Every class in Adapt ultimately inherits from a class called **base** and this class provides a few really useful features that can make this impossible, possible.

### Properties
Typically in PHP we would define a property like so:
```php
class some_class{
    public $some_property = "foobar";
}

$class = new some_class();
print $class->some_propery;
```
And of course this works just fine.  Adapt provides via **base** the ability to make methods act as properties and also to make properties, read or wrote only.

To create a readable property you simply prefix a method with **pget_** and this method will now act a property getter, prefixing a method with **pset_** will transform the method to a property setter.

Because we can use methods as properties we are also able to do things when properties are accessed.

In addition to **pget_** and **_pset** there also exists another, **mget_**, this used only available in models and so you should read how it works there.

Heres a simple example, note that we have to inherit from **base** for this to work.
```php
class some_class \adapt\base{
    protected $_the_inner_propery;
    
    public function pget_some_property(){
        return $this->_the_inner_propery;
    }
    
    public function pset_some_property($value){
        $this->_the_inner_property = $value;
    }
}

$class = new some_class();
$class->some_property = "foobar";
print $class->some_property;
```

### Extending
Adapts **base** provides a way to extend classes at runtime, this is useful because of the way bundles work in Adapt, more on that in the bundles documentation.

Lets look at an example of extending a class and adding a new method to it.
```php
class some_class extends \adapt\base{

}

// Instanciate the class
$class = new some_class();

// Add a new method
some_class::extend(
    'new_method',
    function($_this, $param1, $param2){
        return $param1 + $param2;
    }
);

// Call the method
$result = $class->new_method(1, 2);
```

Note that the method defintion has the first parameter of **$_this** which is the object to which our method is attached.  Normally inside an object we would use **$this**, however, **$this** is a keyword and so we cannot use.

