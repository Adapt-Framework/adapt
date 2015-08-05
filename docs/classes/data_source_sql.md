# Abstract class `sql_data_source`
**Inherits from:** [data_source](/docs/classes/data_source.md) > [base](/docs/classes/base.md)

**Implements:** [data_source_sql](/docs/interfaces/data_source_sql.md)

`data_source_sql` is a foundation class for building SQL data sources. When building a new SQL data source you can either inherit from this class (or one of it's children) or you can implement the interface [data_source_sql](/docs/interfaces/data_source_sql.md).

## Table of contents

## Contructing

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
### EVENT_HOST_DISCONNECT
### EVENT_QUERY

## Properties
### schema (R/W)
`array()` containing the schema.

### data_types (R/W)
`array()` of data types


## Methods
### get_number_of_datasets()
Returns a count of all the data sets available to this data source.

#### Returns:
- Integer

--

### get_dataset_list()
Returns a list of the all the dataset available to this data source.

#### Returns
- `array()`

--

### get_number_of_rows(`$dataset_index`)
Returns the number of rows in the given `$dataset_index`.

#### Input:
- `$dataset_index` The index or name of the dataset.

#### Returns:
- Integer

--

### get_row_structure(`$dataset_index`)
Returns an array containin the row structure of `$dataset_index`.

#### Input:
- `$dataset_index` The index or name of the dataset.

#### Returns:
- `array()`

--

### get_reference(`$table_name`, `$field_name`)
If `$table_name`.`$field_name` is a foreign key and array is returned containing the table name and field name that `$table_name` and `$field_name` relate too.

#### Input:
- `$table_name` The table or dataset you wish to find a relationship for.
- `$field_name` The field name you wish to find a relationship for.

#### Returns:
- If successful `array('table_name' => 'THE NAME OF THE TABLE', 'field_name' => 'THE NAME OF THE FIELD')` is returned, else `array()` is returned.

--

### get_referenced_by(`$table_name`, `$field_name`)
If `$table_name`.`$field_name` is a primary key, this function returns a list of all tables and fields that reference `$table_name`.`$field_name`.

#### Input:
- `$table_name` The table or dataset you wish to find a relationship for.
- `$field_name` The field name you wish to find a relationship for.

#### Returns:
- If successful `array(array('table_name' => 'THE NAME OF THE TABLE', 'field_name' => 'THE NAME OF THE FIELD'), ...)` is returned, else `array()` is returned.

--

### get_relationship(`$table1`, `$table2`)
Returns the relationships that exist between two tables.

#### Input:
- `$table1` The first table.
- `$table2` The second table.

#### Returns:
- `array()` containing a list of relationships, if any.

--

## get_data_type(`$data_type`)
Returns an array of information about a particular data type.

#### Input:
- `$data_type` The name or ID of a data type.

#### Returns:
- `array()` containing the data type information.

--

### get_data_type_id(`$data_type`)
Returns the ID for a particular data_type.

#### Input:
- `$data_type` The name of a data type

#### Returns:
- Integer

--

