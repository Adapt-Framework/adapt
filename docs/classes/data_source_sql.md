# Abstract class `data_source_sql`
**Inherits from:** [data_source](/docs/classes/data_source.md) > [base](/docs/classes/base.md)

**Implements:** [data_source_sql](/docs/interfaces/data_source_sql.md)

`data_source_sql` is a foundation class for building SQL data sources. When building a new SQL data source you can either inherit from this class (or one of it's children) or you can implement the interface [data_source_sql](/docs/interfaces/data_source_sql.md).

## Table of contents

## Contructing
### __construct(`$host = null`, `$username = null`, `$password = null`, `$schema = null`, `$read_only = false`)
Constructs a new `data_source_sql` and optionally adds a host.

#### Input:
- `$host` (Optional) The SQL host to connect to.
- `$username` (Optional) The username for the SQL host
- `$password` (Optional) The password for the SQL host
- `$schema` (Optional) The default schema to use.
- `$read_only` (Optional) Boolean indicating if the SQL host should be treated as read only.


## Constants
### Event constants
```php
const EVENT_HOST_CONNECT = 'adapt.host_connect';
const EVENT_HOST_DISCONNECT = 'adapt.host_disconnect';
const EVENT_QUERY = 'adapt.query';
```
### Fetch constants
```php
const FETCH_ASSOC = 1;
const FETCH_ARRAY = 2;
const FETCH_OBJECT = 3;
const FETCH_ALL_ASSOC = 4;
const FETCH_ALL_ARRAY = 5;
const FETCH_ALL_OBJECT = 6;
```

## Events
[Learn more about event handling in Adapt](/docs/events.md)

### EVENT_HOST_CONNECT
Occurs whenever a connection is made to a SQL host.

### EVENT_HOST_DISCONNECT
Occurs whenever a SQL host is disconnected.

### EVENT_QUERY
Occurs whenever a query is execute.

## Properties
### sql
Shorthand property for getting a new `sql` object.

You can construct a `sql` object against a particular data source by specifying the data source in the constructor:
```php
/* Create a data_source */
$data_source = new data_source_mysql(/* Connection Params */);

/* Create a sql object */
$sql = new sql(null, $data_source);

/* Execute a statement */
$results = $sql->select('*')->from('foo')->execute()->results();
```

Or you could use the shorthand property:
```php
/* Create a data_source */
$data_source = new data_source_mysql(/* Connection Params */);

/* Execute a query */
$results = $data_source->sql->select('*')->from('foo')->execute()->results();

```

## Methods
### sql(`$statement = null`)
Shortcut function for quick execution of SQL statement.

#### Input:
- `$statement` (Optional) A SQL string or an instanceof [sql](/docs/classes/sql.md).

#### Returns:
- New instance of [sql](/docs/classes/sql.md)

#### Examples:
Getting a new sql instance:
```php
$sql = $data_source->sql();
```

Executing a query directly:
```php
$results = $data_source->sql('select * from foo')->execute()->results();
```

Execute a query using the sql functions:
```php
$results = $data_source->sql()->select('*')->from('foo')->execute()->results();
```

--

### get_primary_keys(`$table_name`)
Returns an array of primary keys (if any) for a particular table.

#### Input:
- `$table_name` The name of the table you'd like to find the primary keys for.

#### Returns:
- `array('key1', 'key2', 'etc...')`

--

### write(`$sql`)
Executes a write statement against the SQL source.  A write statement is anything that causes the data or the structure of a database to change.

#### Input:
- `$sql` The statement to execute, this must be a string and a valid SQL statement for the RDMS.

--

### read(`$sql`)
Executes a read-only SQL statement such as a select.

#### Input:
- `$sql` The statement to execute against the SQL source.  This must be a string and a valid SQL statement.

#### Returns:
- A statement handle that is needed to retrieve the data.

--

### query(`$sql`, `$write = false`)
Executes a SQL statement against the data source.  Please use `read` or `write` instead of this function.

#### Input:
- `$sql` The statement to execute, this must be a string and a valid SQL statement.
- `$write` (Optional) Is this statement going to change any data?

#### Returns:
- When `$write = false` a statement handle is returned.

--

### fetch(`$statement_handle`, `$fetch_type = self::FETCH_ASSOC`)
Fetches data from a statement handle.

#### Input:
- `$statement_handle` The statement handle returned from `read()` or `query()`.
- `$fetch_type` (Optional) How would you like the data returned? [See fetch constants for available options](#fetch-constants)

#### Returns:
- Returns an array of data formated according to `$fetch_type`.

--

### last_insert_id()
Returns the last insert ID from an insert statement.

#### Returns:
- Integer

--

### add_host(`$host`, `$username`, `$password`, `$schema`, `$read_only = false`)
Adds additional hosts for the data source.  This is useful in Master/slave setups or for clustered RDMS.  When using in a Master / slave setup ensure that the slaves are set to read only and the Master set to write.
When multiple hosts are in use the work is load balanced automatically between the hosts.

#### Inputs:
- `$host` (Optional) The SQL host to connect to.
- `$username` (Optional) The username for the SQL host
- `$password` (Optional) The password for the SQL host
- `$schema` (Optional) The default schema to use.
- `$read_only` (Optional) Boolean indicating if the SQL host should be treated as read only.

--

### connect(`$host`)
Opens a connection to a host. There is no need to call this directly as it is automatically called when a host is added and queried for the first time.

#### Inputs:
- `$host` An associative array with the following keys:
    - `host` The host name or IP address.
    - `username` The username to connect to the data source.
    - `password` The password for the data source.
    - `schema` The schema to use.

#### Returns:
- Either:
    - `false` When a connection could not be made.
    - A PHP database object such as `mysqli`

--

### disconnect(`$host`)
Closes a connection to a host.  There is no need to call this function directly as it will be automatically called when the data source is destroyed.

#### Inputs:
- `$host` An associative array with the following keys:
    - `host` The host name or IP address.
    - `username` The username to connect to the data source.
    - `password` The password for the data source.
    - `schema` The schema to use.

--

### get_host(`$writable = false`)
Gets a random host from the list of hosts, optionally filtered by `$writable`.

#### Input:
- `$writable` (Optional) Should the host be capable of writing?

#### Returns:
- Either:
    - `null` if no host is available
    - `array()` with the following keys:
        - `host` The name of the host.
        - `username` The username of the data source
        - `password` The password of the data source
        - `schema` The schema to connect to.
        - `read_only` Boolean indicating if the host is read only or not
        - `handle` A PHP object that represents the acctual connection.  For MySQL this could be a `mysqli` instance.

--

### render_sql(`$sql`)
Converts an instance of `sql` to a SQL string for the current RDMS.

#### Input:
- `$sql` The `sql` object to be converted.  This must be an instance of [sql](/docs/classes/sql.md) or an exception is thrown.

#### Returns:
- A string containing the acctual SQL statement.

#### Example:
```php
/* Connect to a data source */
$data_source = new data_source_mysql(/* Connection params */);

/* Build a sql object */
$sql = $data_source->sql->select('*')->from('foo');

/* Convert the object to a SQL statement */
$statement = $data_source->render_sql($sql);

/* Print out the statement */
print $statement;
```
Prints out ***SELECT * FROM foo;***

--

### escape(`$string`)
Escapes a string and makes it SQL injection safe.

#### Input:
- `$string` A string.

#### Returns:
- A string

--

### validate(`$table_name`, `$field_name`, `$value`)
Validate a value against the defined data type for the field.  This only checks if the value is valid, it does not check for dependencies or mandatory groups.
[Learn more about data types](/docs/data_types.md)

#### Input:
- `$table_name` The name of the table where the value is to be stored.
- `$field_name` The field name where the value is to be stored.
- `$value` The value that you would like to validate.

#### Returns:
- `true` or `false`

--

### format(`$table_name`, `$field_name`, `$value`)
Formats a value based on it's data type.
[Learn more about data types](/docs/data_types.md)

#### Input:
- `$table_name` The name of the table where the value is to be stored.
- `$field_name` The field name where the value is to be stored.
- `$value` The value that you would like to format.

#### Returns:
- The formatted value

--

### unformat(`$table_name`, `$field_name`, `$value`)
Unformats a value based on it's data type.
[Learn more about data types](/docs/data_types.md)

#### Input:
- `$table_name` The name of the table where the value is to be stored.
- `$field_name` The field name where the value is to be stored.
- `$value` The value that you would like to unformat.

#### Returns:
- The unformatted value.

--

## convert_data_type(`$type`, `$signed = true`, `$zero_fill = false`)
Converts an adapt data type to a data type the RDBMS understands.
[Learn more about data types](/docs/data_types.md)

#### Input:
- `$type` The adapt data type to be converted
- `$signed` (Optional) If the value is numeric should it be signed?
- `$zero_fill` (Optional) Should the data type be zero filled?

#### Returns:
- A string representing the RDBMS data type.


