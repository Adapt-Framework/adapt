## base
Base is the foundation for the entire framework, all classes using the framework ultimately inherit from this one.  Base has no dependecies.

### Properties

#### instance_id
Provides a unqiue ID for this class, this property is read-only and is subject to change between each request.

--
#### dom
Provides access to the Document Object Model before it is dispatched to the client, this property is often an instance of [page](/docs/views/page.md) but can be anything that is printable.  This property can be changed allowing the entire DOM to be swapped out at any time.

--
#### data_source
Provides access to the database if a connection has been defined.  This property should always conform to the interface [data_source](/docs/interfaces/data_source) and can be changed at runtime.

--
#### file_store
Provides access to file storage.  The default object returned is [storage_file_system](/docs/classes/storage_file_system.md), this object stores files on the file system.  You can change the default to another object if you'd like to store files in a database or on some CDN.

--
#### cache
Returns an instance of [cache](/docs/classes/cache.md)

--
#### request
Returns the current request, equivilent to $_REQUEST.

--
#### response
Returns any responses we are issuing to the client, think of this as the opposite to the **request** property.

--
#### files
Equivilent to $_FILES

--
#### sanitize
Returns an instance of [sanitizer](/docs/classes/sanitizer.md)

### Events
#### EVENT_READY = `"adapt.ready"`
Is fired when the framework and all bundles have been booted.

--
#### EVENT_ERROR = `"adapt.error"`
Is fired whenever an error occurs.

### Methods
#### add_handler(`$namespace_and_class_name`)
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

##### INPUT:
- `$namespace_and_class_name` The full namespace and class name of the class you wish to register as a handler.

##### RETURNS:
- `true` or `false`

--

#### error(`$error`)
Registers a new error and triggers the event `EVENT_ERROR`.

##### INPUT:
- `$error` The error message.

##### RETURNS:
- `null`

--

#### errors(`[$clear = false]`)
Returns a list of errors that have occured.

##### INPUT:
- `$clear` (Optional) Should the list of errors be reset after returning?

##### RETURNS:
- `array(...)` of error messages.

--

#### on (`$event_type`, `$function`, `[$data = null]`)
Adds an event handler to an event.

##### INPUT:
- `$event_type` The event that occured
- `$function` The function to handle the event, this function should return false if you wish to prevent the event from bubbling.
- `$data` (Optional) Any data you want passed the handler when it's called.

##### RETURNS:
- `null`

##### Example:
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

#### trigger(`$event_type`, `[$event_data = array()]`)
Fires an event.

##### INPUT:
- `$event_type` The type of event to trigger
- `$event_data` (Optional) An array of items to be passed to any event handlers for this event type.

##### RETURNS:
- `null`

--

#### store(`$key`, `[$value = null]`)
Stores or retrieves data, all data stored is temporary and exists only for the current request.  Data stored with this function can be access via any other object, not just the object that initially set it.  When using only the `$key` param the function returns any values associated with the key, when both are specified the value is stored against the key.

##### INPUT:
- `$key` A unique key to identify the data
- `$value` (Optional) The value to be stored againist the key.

##### RETURNS:
When both params is set it returns `null`.  When only `$key` is set it returns whatever data was stored.

##### Example:
```php
$base = new base();
$base->store('my_key', 'Hello world');

$model = new model_field();
print $model->store('my_key');
```

**Prints out:**
`Hello world`

--



### Static functions
