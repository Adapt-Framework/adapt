# base
`base` is the foundation class for the entire framework, all classes using the framework ultimately inherit from this one.


## Table of contents
[Functionality](#functionality)
- [Dynamic properties](#dynamic-properties)

[Properties](#properties)
- [instance_id](#instance_id)
- [dom](#dom)
- [data_source](#data_source)
- [file_store](#file_store)
- [cache](#cache)
- [request](#request)
- [response](#response)
- [files](#files)
- [sanitize](#sanitize)

[Events](#events)
- [Ready](#event_ready--adaptready)
- [Error](#event_error--adapterror)

[Methods](#methods)
- [add_hander](#add_handlernamespace_and_class_name)
- [error](#errorerror)
- [errors](#errorsclear--false)
- [on](#onevent_type-function-data--null)
- [trigger](#triggerevent_type-event_data--array)
- [store](#storekey-value--null)
- [remove_store](#remove_storekey)
- [setting](#settingkey-value--null)
- [settings](#settings)
- [remove_setting](#remove_settingkey)
- [redirect](#redirecturl-pass_on_response--true)
- [request](#requestkey-value--null)
- [response](#respondaction-response)
- [cookie](#cookiekey-value--null-expires--0-path--)

[Static functions](#static-functions)
- [create_object](#create_object-class)
- [extend](#extendfunction_name-function)

## Functionality
### Dynamic properties
In adapt properties can be functions as well as being able to make them read only.

Typically in PHP if you wanted to add a property called `foobar` you would do something like this:
```php
class my_class_name extends \frameworks\adapt\base{
    
    public $foobar;
    
}
```

Thats works well unless you want to do something when a property is set or if you'd like to make it read only.

Adapt provides two prefix functions to achieve this, the first `aget_` is used as a getter and `aset_` is used as a setter.  To add a read only property all you need to do is this:

```php
class my_class_name extends \frameworks\adapt\base{
    
    /* Make the property private */
    private $_foobar;
    
    /* Add the getter */
    public function aget_foobar(){
        return $this->_private;
    }
}

/* Lets create a new instance */
$class = new my_class_name();

/* Get the property */
print $class->foobar;
```

What if you wanted to do something everytime `foobar` is set?
```php
class my_class_name extends \frameworks\adapt\base{
    
    /* Make the property private */
    private $_foobar;
    
    /* Add the getter */
    public function aget_foobar(){
        return $this->_private;
    }
    
    /* Add the setter */
    public function aset_foobar($value){
        /*
         * Do something here
         */
        
        $this->_foobar = $value;
    }
}

/* Lets create a new instance */
$class = new my_class_name();

/* Lets set the property */
$class->foobar = 'Hello world';
```



## Properties

### instance_id
Provides a unqiue ID for this class, this property is read-only and is subject to change between each request.

--
### dom
Provides access to the Document Object Model before it is dispatched to the client, this property is often an instance of [page](/docs/views/page.md) but can be anything that is printable.  This property can be changed allowing the entire DOM to be swapped out at any time.

--
### data_source
Provides access to the database if a connection has been defined.  This property should always conform to the interface [data_source](/docs/interfaces/data_source) and can be changed at runtime.

--
### file_store
Provides access to file storage.  The default object returned is [storage_file_system](/docs/classes/storage_file_system.md), this object stores files on the file system.  You can change the default to another object if you'd like to store files in a database or on some CDN.

--
### cache
Returns an instance of [cache](/docs/classes/cache.md)

--
### request
Returns the current request, equivilent to $_REQUEST.

--
### response
Returns any responses we are issuing to the client, think of this as the opposite to the **request** property.

--
### files
Equivilent to $_FILES

--
### sanitize
Returns an instance of [sanitizer](/docs/classes/sanitizer.md)

## Events
### EVENT_READY = `"adapt.ready"`
Is fired when the framework and all bundles have been booted.

--
### EVENT_ERROR = `"adapt.error"`
Is fired whenever an error occurs.

## Methods
### add_handler(`$namespace_and_class_name`)
Registers a new class handler, class handlers are classes that are used generate new classes on demand.

The [html](/docs/classes/html) class is a good example.

**Without a handler:**
```php
$p = new html('p');
```

**With a handler:**
```php
$p = new html_p();
```
The class `html_p` never existed but using a handler we can still call it.  [Learn more about handlers here](/docs/handlers)

#### INPUT:
- `$namespace_and_class_name` The full namespace and class name of the class you wish to register as a handler.

#### RETURNS:
- `true` or `false`

--

### error(`$error`)
Registers a new error and triggers the event `EVENT_ERROR`.

#### INPUT:
- `$error` The error message.

#### RETURNS:
- `null`

--

### errors(`[$clear = false]`)
Returns a list of errors that have occured.

#### INPUT:
- `$clear` (Optional) Should the list of errors be reset after returning?

#### RETURNS:
- `array(...)` of error messages.

--

### on (`$event_type`, `$function`, `[$data = null]`)
Adds an event handler to an event.

#### INPUT:
- `$event_type` The event that occured
- `$function` The function to handle the event, this function should return false if you wish to prevent the event from bubbling.
- `$data` (Optional) Any data you want passed the handler when it's called.

#### RETURNS:
- `null`

#### Example:
```php
$adapt = new base();

$adapt->on(
    base::EVENT_READY,
    function($event){
        print "Event " . $event['event_type] . " was fired";
    }
);
```

--

### trigger(`$event_type`, `[$event_data = array()]`)
Fires an event.

#### INPUT:
- `$event_type` The type of event to trigger
- `$event_data` (Optional) An array of items to be passed to any event handlers for this event type.

#### RETURNS:
- `null`

--

### store(`$key`, `[$value = null]`)
Stores or retrieves data, all data stored is temporary and exists only for the current request.  Data stored with this function can be access via any other object, not just the object that initially set it.  When using only the `$key` param the function returns any values associated with the key, when both are specified the value is stored against the key.

#### INPUT:
- `$key` A unique key to identify the data
- `$value` (Optional) The value to be stored againist the key.

#### RETURNS:
When both params is set it returns `null`.  When only `$key` is set it returns whatever data was stored.

#### Example:
```php
$base = new base();
$base->store('my_key', 'Hello world');

$model = new model_field();
print $model->store('my_key');
```

**Prints out:**
`Hello world`

--

### remove_store(`$key`)
Removes any data that was stored using `base::store(...)`.

#### INPUT:
- `$key` The key associcated with the data that you'd like to remove.

#### RETURNS:
- `null`

--

### setting(`$key`, `[$value = null]`)
Returns the value of a setting or sets a new value.  Settings are defined by each bundle in the **bundle.xml** file, on boot Adapt loads these settings and makes them available via this function. By providing only the `$key` the function will return the value, when both `$key` and `$value` are provided the function changes the setting to the value of `$value`.  This is only effective for the current request, to change a setting perminatly either set in your bundle ([Learn more about bundles](/docs/bundles.md)) or add it to the global settings file ([Learn more about gloabl settings](/docs/settings.md)).

#### INPUT:
- `$key` A unique key to identify the setting
- `$value` (Optional) The value to be stored againist the key, this must be a string, a number or an array or strings/numbers.

#### RETURNS:
When both params is set it returns `null`.  When only `$key` is set it returns whatever the value of the setting is.

--

### settings()
Returns an array of all settings.

#### RETURNS:
`array( 'key' => 'value', ...)`

--

### remove_setting(`$key`)
Removes any setting that was set using `base::setting(...)` or set by a bundle.  This is only for the current request, on the next request the setting will be back to it's original value.  

#### INPUT:
- `$key` The key associcated with the data that you'd like to remove.

#### RETURNS:
- `null`

--

### redirect(`$url`, `$pass_on_response = true`)
Performs an HTTP redirect to the new `$url`.

#### INPUT:
- `$url` The URL to redirect too.
- `$pass_on_response` (Optional) Should the response be passed on to the new URL?  **PLEASE NOTE:** When redirecting to an external site you should set this param to `false` else you may accidently pass on information that would otherwise be private.

#### RETURNS:
Never returns, the application exits upon redirecting.

--

### request(`$key`, `$value = null`)
Updates or returns a value in the request property.

#### INPUT:
- `$key` The key for the value you wish to retrieve or set.
- `$value` (Optional) The value you would like to set.

#### RETURNS:
When `$value` is `null` the value for associated with the `$key` is returned, when `$value` is set `null` is returned.

--

### respond(`$action`, `$response`)
Sets a response to an action. [Learn more about actions](/docs/actions.md).

#### INPUT:
- `$action` The full path of the action you wish to respond to.
- `$response` The response you wish to return to the client, this is usually an array but can be anything.

#### RETURNS:
- `null`

--

### cookie(`$key`, `$value = null`, `$expires = 0`, `$path = '/'`)
Sets or retrieves a cookie.  When `$value` is set the cookie is set, when `$value` is `null` the current cookie value is returned.

#### INPUT:
- `$key` The name of the cookie.
- `$value` (Optional) The value to be set
- `$expires` (Optional) The number of seconds this cookie should persist for, specifing `0` means the cookie is for the current session only.
- `$path` (Optional) Should the cookie be restricted to a particular path?

#### RETURNS:
When `$value` is `null` the value for associated with the `$key` is returned, when `$value` is set `null` is returned.

--


## Static functions
### create_object(`$class`)
Create a new instance of a class from it's name.

#### INPUT:
- `$class` The name of the class you wish to create.

#### RETURNS:
- Returns a new instance of `$class`. If the class doesn't exist `null` is returned.

**Example**
```php
$adapt = new base();
$sql_object = $adapt->create_object('sql');

```

--

### extend(`$function_name`, `$function`)
Allows new [properties](/docs/properties.md) or methods to be added to classes at runtime.  [Learn more about extending classes](/docs/extending.md)

#### INPUT:
- `$function_name` The name of the function you wish to extend.
- `$function` The function to handle the method or property, the first param should be `$_this` which is a reference to the class being extended.  Please note that by using extend you are only able to access public properties and methods of the target class.

#### RETURNS:
- `null`

**Example:** Lets say you wanted to add a new method `count_statements` to the class `sql`, most of the time you can achieve this with inheritance but that means than any other bundle that would like to use the functionality must either inherit from your `sql` class or create thier own.  In this instance it would be more useful to just extend the original object and thus making the functionallity available to all instances of the class.  Heres how:

```php
sql::extend(
    'count_statements',
    function($_this){
        /* $_this = the sql instance */
        
        /* Do something here and then return */
        $count = 0;
        return $count;
    }
);

/* Calling the function */
$sql = new sql();
print $sql->count_statements();
```
Prints out `0`

**Real world example:** The [sessions](http://github.com/mbruton/sessions) bundle extends `\frameworks\adapt\base` (this class) and adds a `session` property.
