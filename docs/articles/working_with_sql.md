# Working with SQL

This article assumes you've read [working with databases](/docs/articles/working_with_databases.md) and you understand the basics of the `data_source` property.

You can use this article as a reference or you can install the bundle adapt_sql_tutorial which contains a working copy of all the SQL covered in this article.

Before we begin lets assume we have a really simple database with four tables, **manufacturer**, **car**, **option** and **car_option**.

**manufacturer** is a list of car makers with the following fields:
* manufacturer_id (Primary key)
* name
* date_created
* date_modified
* date_deleted

**car** is a list of cars with the following fields:
* car_id (Primary key)
* manufacturer_id (Foreign key joining to manufacturer.manufactured_id)
* name
* date_created
* date_modified
* date_deleted

**option** is a list of available car extras with the following fields:
* option_id (Primary key)
* name
* date_created
* date_modified
* date_deleted

**car_option** is a list of options for a particular car with the following fields:
* car_option_id (Primary key)
* car_id (Foreign key joining to car.car_id)
* option_id  (Foreign key joining to option.option_id)
* date_created
* date_modified
* date_deleted

If you have installed the bundle adapt_sql_tutorial these tables will have been created and populated with data for you.

## Creating the sql object
You can define the sql object as simply as:
```php
$sql = sql();
```

And in most cases this will work just fine, if however you are using multiple database connections you may not be querying the correct source.

To solve this the `sql` object will accept two construction parameters, the first is a SQL statement, the second is a `data_source_sql` object representing the database connection.  So we could construct the object like this:
```php
/* Without a statement */
$sql = new sql(null, $this->data_source);

/* With a statement */
$sql = new sql("select * from car", $this->data_source);
```

You'll remember for [working with databases](/docs/articles/working_with_databases.md) that `data_source` is a shared property between all objects holding the current database connection.

There is another way to construct that is more flexible:
```php
/* Without a statement */
$sql = $this->data_source->sql; //Acting as a property

/* With a statement */
$sql = $this->data_source->sql("select * from car"); //Acting as a function
```

## Chainable calls
The `sql` object is chainable meaning most of it's methods return itself.

To execute a query you call the `execute()` method.  To get data you can call the `results()` methods.
```php
/* Without chaining */
$sql = $this->data_source->sql("select * from car");
$sql->execute();
$results = $sql->results();

/* With chaining we can do it in a single line of code */
$results = $this->data_source->sql("select * from car")->execute()->results();
```

For obvious reasons the `results()` method isn't chainable.

## Caching results
The `sql` object generally handles caching on you behalf.  You can change this by providing a single param to the `execute()` method with the number of seconds to cache the statement.

Providing the value `0` means do not cache.  Providing `null` means to use the system default defined in the setting **adapt.sql_cache_expires_after** which be default is set to the value `60`.

```php
/* Use the default cache time */
$results = $this->data_source->sql("select * from car")->execute()->results();

/* Do not cache results */
$results = $this->data_source->sql("select * from car")->execute(0)->results();

/* Cache the results for five minutes */
$results = $this->data_source->sql("select * from car")->execute(300)->results();
```

## Selecting
In the previous examples we looked at how to run a simple select statement. This is ok but writing SQL that may not run on a different database platforms makes your code less portable. If you intend to publish your bundle to the Adapt respository then you need to ensure your code works with all the major database platforms, heres how.

```php
/* The bad way */
$results = $this->data_source->sql("select * from car")->execute()->results();

/* The best way */
$results = $this->data_source->sql->select('*')->from('car')->execute()->results();
```

The `sql` object is printable, so if you need to see the statement you can just print the object which will print out the correct SQL for the current database connection.

**NOTE:** You can't print the object once it has been executed, well you can, it will just be very blank.
```php
$sql = $this->data_source->sql->select('*')->from('car');
print $sql;
```

Prints out:
```sql
SELECT * FROM car
```

### Making strings safe
The `sql` object has a shortcut static function `q` used for quoting strings.
```php
print sql::q("Hello world");
```
Outputs
```
"Hello world"
```

### Class handling

The `sql` objected a registered [class handler](/docs/articles/working_with_class_handlers.md) so it means we can create objects that have never been declared and have them translated to SQL on our behalf.

In this example, the `sql_and` object doesn't exist yet it is converted into SQL just fine.
```php
print new sql_and(1, 2, 3);
```

Outputs:
```sql
(1 AND 2 AND 3)
```

Some more examples
```php
/* Logical */
print new sql_or(1, 2, 3);
print new sql_between('some_field_name', 50, 100);

/* Nesting */
print new sql_and(new sql_or(1, 2, 3), 4, 5);

/* Keywords */
print new sql_null();
print new sql_true();

/* String functions */
print new sql_concat('some_field_name', sql::q('some string'));
print new sql_trim(sql::q("  Oh my  "));

/* Conditions */
print new sql_cond('some_field', sql::EQUALS, sql::q('some value'));
print new sql_condition('another_field', sql::NOT_EQUALS, sql::q('another value'));

/* If statement */
print new sql_if(new sql_cond('field', sql::GREATER_THAN, 50), sql::q("Foo"), sql::q("Bar"));

```

Becomes:
```sql
# Logical
(1 OR 2 OR 3)
(some_field_name BETWEEN 50 AND 100)

# Nesting
((1 OR 2 OR 3) AND 4 AND 5)

# Keywords
NULL
TRUE

# String functions
CONCAT(some_field_name, "some string")
TRIM("   Oh My   ")

# Conditions
some_field = "some value"
another_field != "another value"

# If statement
IF ('field' > 50, "Foo", "Bar")
```

Here is a list of fully supported functions:

String functions    | Numeric functions     | Date & time functions
--------------------|-----------------------|-----------------------
ascii               | abs                   | current_date 
char                | acos                  | current_time
concat              | asin                  | current_timestamp
format              | atan                  | now
length              | atan2                 | 
lower               | ceil                  |
ltrim               | cos                   | 
replace             | exp                   | 
reverse             | floor                 | 
rtrim               | power                 | 
substr              | round                 | 
trim                | sign                  |
upper               | sin                   |
                    | tan                   |


