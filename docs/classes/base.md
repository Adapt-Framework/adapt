## base
Base is the foundation for the entire framework, all classes using the framework ultimately inherit from this one.  Base has no dependecies.

### Properties

### Events

### Methods
#### instance_id
Provides a unqiue ID for this class, this property is read-only and is subject to change between each request.

#### dom
Provides access to the Document Object Model before it is dispatched to the client, this property is often an instance of [page](/docs/views/page.md) but can be anything that is printable.  This property can be changed allowing the entire DOM to be swapped out at any time.

#### data_source
Provides access to the database if a connection has been defined.  This property should always conform to the interface [data_source](/docs/interfaces/data_source) and can be changed at runtime.

#### file_store
Provides access to file storage.  The default object returned is [storage_file_system](/docs/classes/storage_file_system.md), this object stores files on the file system.  You can change the default to another object if you'd like to store files in a database or on some CDN.

#### cache
Returns an instance of [cache](/docs/classes/cache.md)

#### request
Returns the current request, equivilent to $_REQUEST.

#### response
Returns any responses we are issuing to the client, think of this as the opposite to the **request** property.

#### files
Equivilent to $_FILES

#### sanitize
Returns an instance of [sanitizer](/docs/classes/sanitizer.md)



### Static functions
