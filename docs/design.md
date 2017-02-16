# Adapt Design

Before developing with Adapt it's helpful to understand how Adapt is designed and what the design enables you to do.  Adapt is flexible and almost everything can be modified or replaced.  It's possible to use Adapt to bootstrap more complex applications because, by design, Adapt can be replaced entirely.

## The foundations
Every class in Adapt ultimately inherits from a class called **base** and this class provides a few really useful features that can make this impossible, possible.

### Adapt properties
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

### Shared properties
Adapt allows properties to be shared between all instances of a class, or between all instances of all classes.  Before we create a shared property we must first store the data somewhere, introducing Adapt store, a runtime data store using a key/value pair. The store is available to all instances of **base**, direct or via inheritance.

```php
$object = new \adapt\base();
$object->store('foo', 'bar');
print $object->store('foo');
```
Using Adapt properties we can make this accessible as a property and thus we have a shared property.

```php
class some_class extends \adapt\base{

    public function pget_foo(){
        return $this->store('foo');
    }
    
    public function pset_foo($value){
        $this->store('foo', $value);
    }

}

$class1 = new some_class();
$class2 = new some_class();

$class1->foo = 'bar';
print $class2->foo; // Prints 'bar'
```

To make this property available to all instance of all classes we can used the **extend** method and extend **base**.

```php
// Define a class
class some_class1 extends \adapt\base{

}

// And another
class another_class extends \adapt\base{

}

// Extend base and add a property getter
\adapt\base::extend(
    'pget_foo',
    function($_this){
        return $_this->store('foo');
    }
);

// Now add a setter
\adapt\base::extend(
    'pset_foo',
    function($_this, $value){
        $_this->store('foo', $value);
    }
);

// Create a two differnt classes
$class1 = new some_class();
$class2 = new another_class();

// Set the property on one
$class1->foo = 'bar';

// Get it from the other
print $class2->foo; // Prints 'bar', how cool is that?
```

## Class handlers
Adapt provides a useful feature called class handlers, class handlers are a way of templating classes at runtime as well as allowing them to cross namespace boundaries seemlessly.

Before we look how to create a class handler, lets look at some that Adapt defines. 

**\adapt\html** is a registered class handler, here it is in action:
```php
$p = new html_p("This class doesn't exist");
$strong = new html_strong("Neither does this one");
$p->add($strong);
$container = new html_div($p); // Again this class isn't defined
// And all the above will work just fine :)
```
So using class handlers we can create classes on demand, all the classes created will have the same functionallity as the class handler, in this case **\adapt\html**.  Once a class has been registered as a handler any class prefixed with the same name and **_** in any namespace will be sent to **\adapt\html** for handling.

Of course there will always be times when you need to concreate a class being handled for custom functionallity.  Lets say we want to change the default behaviour og **html_p** so that it always includes the css class 'foo', we just need to define the class and extend the class handler.  The class should be declared in your bundles namespace, it will however transcend this limitation at runtime and become the default across all namespaces.

Heres the code:
```php
namespace some_namespace;

class html_p extends \adapt\html{

    public function __construct($items = null){
        parent::__construct('p', $items, ['class' => 'foo']);
    }

}
```

If we to create this class from another namespace, we would in fact get the concreate from the above class.
```php
namespace another_namespace; // Different namespace
$p = new html_p("Hello world"); // Not defined in this namespace
print $p;
```

Prints outs:
```html
<p class="foo">Hello world</p>
```

Lets look at another handler, **\adapt\model**. This handler allows you to use database tables as if they were predefined models.  Suppose a table exists called **car** and it has the fields car_id, manufacturer and model, we can easily create an instance to handle this without ever defining the class.

```php
namespace some_namespace;

$car = new model_car(); // Class doesn't exist
$car->manufacturer = 'Ford';
$car->model = 'Capri';
$car->save(); // Writen to the database

print $car->car_id;
```

As with the **\adapt\html** handler we can concreate a specific model if we need custom functionality, and again being a class handler the concreated class will transcend namespaces.

### Defining a class handler
Typically would define a handler when your bundle boots, you can read about booting in the bundle documentation.

To create a class handler is really simple, **\adapt\base** has the method **add_handler** which takes a single string parameter with the full namespace and class name of the class to be registered.

Lets create a new handler called **hello**, first we need to create a class.
```php
namespace some_namespace;

class hello extends \adapt\base{
    
    // The constructor must have at least one parameter that
    // will receive everything after $x = new hello_...();
    // you may add additional parameters and these parameter
    // will be transposed to any handled class.
    public function __construct($class_name){
    
    }
}
```

We now need to register the class as a handler, since **\adapt\base** is the foundation for all other classes, we can from within any class call **$this->add_handler(...)**, typically this would be done when the bbundle is booting.
```php
$this->add_handler("\\some_namespace\\hello");
```

And so lets try it out:
```php
namespace another_namespace;

$hello_world = new hello_world(); // Works just fine :)
```

## File storage
**\adapt\base** has a shared property called **file_store** which provides access to an instance of **\adapt\storage_file_system** which can be used to store files on the file system.  

While often people deploy there own file storage solutions, please don't, Adapt file storage is both simple and flexible, because it's a shared property, all classes have immediate access and so all bundles store data in the same place.  And because you may want to store files else where, such as in a database, you can just include the bundle **storage_database** in your dependency list and then all files are stored in the database.

In fact the storage interface is very simple so you can also write your own file storage layer and replace the Adapt default.  Your storage engine must conform to **\adapt\interfaces\storage_file** and to replace the default you simply do this when your bundle boots:
```php
$this->file_store = new your_storage_class();
```

## Database access
To define a database connection you add the connection settings to your settings.xml, you can read how in the bundles documentation, once defined, **\adapt\base** provides a shared property **data_source** which can give us access to the database.

So lets execute a simple SQL query:
```php
$results = $this->data_source->sql->select('*')->from('car')->execute()->results();
```
Notice it's a single line of code to define, execute and get the results.  We're not showing off, if we were showing off we could have printed it to a html table in one line of code, like so:

```php
print new \adapt\view_table($this->data_source->sql->select('*')->from('car')->execute()->results());
```

For more information on accessing data, see the section on **SQL**.
