# Abstract class `data_source`
**Inherits from:** [base](/docs/classes/base.md)

**Implements:** [data_source](/docs/interfaces/data_source.md)

`data_source` is a foundation class for building data sources. When building a new data source you can either inherit from this class (or one of it's children) or you can implement the interface [data_source](/docs/interfaces/data_source.md).

## Table of contents
- [Properties](#properties)
    - [schema](#schema)
    - [data_types](#data_types)
- [Methods](#methods)
    - [get_number_of_datasets](#get_number_of_datasets)
    - [get_dataset_list](#get_dataset_list)
    - [get_number_of_rows](#get_number_of_rowsdataset_index)
    - [get_row_structure](#get_row_structuredataset_index)
    - [get_field_structure](#get_field_structuredataset_index-field_name)
    - [get_reference](#get_referencetable_name-field_name)
    - [get_referenced_by](#get_referenced_bytable_name-field_name)
    - [get_relationship](#get_relationshiptable1-table2)
    - [get_data_type](#get_data_typedata_type)
    - [get_data_type_id](#get_data_typedata_type)
    - [get_base_data_type](#get_base_data_typedata_type)
    - [get](#getdataset_index-row_offset-number_of_rows)
    

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

### get_data_type(`$data_type`)
Returns an array of information about a particular data type.
[Learn more about working with data_types](/docs/data_types.md)

#### Input:
- `$data_type` The name or ID of a data type.

#### Returns:
- `array()` containing the data type information.

--

### get_data_type_id(`$data_type`)
Returns the ID for a particular data_type.
[Learn more about working with data_types](/docs/data_types.md)

#### Input:
- `$data_type` The name of a data type

#### Returns:
- Integer

--

### get_base_data_type(`$data_type`)
Returns the base data type if the `$data_type` provided is based upon another.
[Learn more about working with data_types](/docs/data_types.md)

#### Input:
- `$data_type` The data type name or ID that you'd like find the base type of.

#### Returns:
- `array()` Containing the root data type.

--

### Get(`$dataset_index`, `$row_offset`, `$number_of_rows = 1`)
Retrieves a record(s) from the data source.

#### Input:
- `$dataset_index` The name or index of the dataset.
- `$row_offset` The index of the record in the dataset.
- `$number_of_rows` (Optional) How many record would you like to retrieve? The default is 1.

#### Returns:
- `array()` of records.

