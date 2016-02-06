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
$sql = new sql(); //Namespace is irrelavent
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

### Putting everything together
We are going to write a statement to select all cars where the manufacturer is Ford and display the car name and the manufacturer name.

```php
$results = $this
    ->data_source
    ->sql
    ->select(
        array(
            'Manufacturer' => 'm.name',
            'Model' => 'c.name'
        )
    )
    ->from('car', 'c')
    ->join('manufacturer', 'm', 'manufacturer_id')
    ->where(
        new sql_cond('m.name', sql::EQUALS, sql::q("Ford"))
    )
    ->execute()
    ->results();
```

Notice how we used a hash array in the select statement so that we could alias the name fields.

**Helpful tip:** If you want to quickly see the output you can do this:
```php
print new \adapt\view_table($results);
```

### Sub-SQL?
Yes, just embed a query in a query.

Taking the previous example, we will change the join to a sub query.
```php
$results = $this
    ->data_source
    ->sql
    ->select(
        array(
            'Manufacturer' => 'm.name',
            'Model' => 'c.name'
        )
    )
    ->from('car', 'c')
    ->join($this->data_source->sql->select('*')->from('manufacturer'), 'm', 'manufacturer_id')
    ->where(
        new sql_cond('m.name', sql::EQUALS, sql::q("Ford"))
    )
    ->execute()
    ->results();
```

### The full list of select features

#### Select
```php
/* Using params */
$sql->select('field_1', 'field_2', 'field_3');

/* Using an array */
$sql->select(array('field_1', 'field_2', 'field_3'));

/* Aliasing with a hash array */
$sql->select(array('alias1' => 'field_1', 'alias2' => 'field_2', 'alias3' => 'field_3'));

/* Chaining */
$sql->select('field_1')->select('field_2')->select(array('field_3', 'field_4', 'field_5'));

/* Sub-SQL */
$sql->select($this->data_source->sql->select('*')->from('car'));

/* Sub-SQL with aliasing */
$sql->select(array('alias' => $this->data_source->sql->select('*')->from('car')));
```

#### Select distinct
```php
/* Using params */
$sql->select_distinct('field_1', 'field_2', 'field_3');

/* Using an array */
$sql->select_distinct(array('field_1', 'field_2', 'field_3'));

/* Aliasing with a hash array */
$sql->select_distinct(array('alias1' => 'field_1', 'alias2' => 'field_2', 'alias3' => 'field_3'));

/* Chaining */
$sql->select_distinct('field_1')->select('field_2')->select(array('field_3', 'field_4', 'field_5'));

/* Sub-SQL */
$sql->select_distinct($this->data_source->sql->select('*')->from('car'));

/* Sub-SQL with aliasing */
$sql->select_distinct(array('alias' => $this->data_source->sql->select('*')->from('car')));
```

#### From
```php
/* Simple */
$sql->from('table_name');

/* Aliasing */
$sql->from('table_name', 'alias');

/* Sub-SQL - Alias becomes mandatory */
$sql->from($this->data_source->select('*')->from->('car'), 'alias');
```

#### Join
```php
/* Simple where the field name is the same in both tables */
$sql->join('table_name', 'alias', 'common_field_name');

/* Conditioned join, where the field name is different in both tables */
$sql->join('table_name', 'alias', new sql_cond('some_field_name', sql::EQUALS, 'some_other_field'));

/* Sub-SQL  */
$sql->join($this->data_source->sql->select('*')->from('table'), 'alias', 'common_field_name');
```

#### Left join
```php
/* Simple where the field name is the same in both tables */
$sql->left_join('table_name', 'alias', 'common_field_name');

/* Conditioned join, where the field name is different in both tables */
$sql->left_join('table_name', 'alias', new sql_cond('some_field_name', sql::EQUALS, 'some_other_field'));

/* Sub-SQL  */
$sql->left_join($this->data_source->sql->select('*')->from('table'), 'alias', 'common_field_name');
```

#### Right join
```php
/* Simple where the field name is the same in both tables */
$sql->right_join('table_name', 'alias', 'common_field_name');

/* Conditioned join, where the field name is different in both tables */
$sql->right_join('table_name', 'alias', new sql_cond('some_field_name', sql::EQUALS, 'some_other_field'));

/* Sub-SQL  */
$sql->right_join($this->data_source->sql->select('*')->from('table'), 'alias', 'common_field_name');
```

#### Inner join
```php
/* Simple where the field name is the same in both tables */
$sql->inner_join('table_name', 'alias', 'common_field_name');

/* Conditioned join, where the field name is different in both tables */
$sql->inner_join('table_name', 'alias', new sql_cond('some_field_name', sql::EQUALS, 'some_other_field'));

/* Sub-SQL  */
$sql->inner_join($this->data_source->sql->select('*')->from('table'), 'alias', 'common_field_name');
```

#### Outer join
```php
/* Simple where the field name is the same in both tables */
$sql->outer_join('table_name', 'alias', 'common_field_name');

/* Conditioned join, where the field name is different in both tables */
$sql->outer_join('table_name', 'alias', new sql_cond('some_field_name', sql::EQUALS, 'some_other_field'));

/* Sub-SQL  */
$sql->outer_join($this->data_source->sql->select('*')->from('table'), 'alias', 'common_field_name');
```

#### Where
```php
/* Simple */
$sql->where(new sql_cond('some_field', sql::EQUALS, sql::q("some value")));

/* Written better */
$sql->where(
    new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
);

/* More complex */
$sql->where(
    new sql_and(
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_or(
            new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
            new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
        )
    )
);

/* With sub SQL */
$sql->where(
    new sql_and(
        new sql_cond('some_field', sql::EQUALS, $this->data_source->sql->select('*')->from('table')),
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_or(
            new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
            new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
        )
    )
);
```

#### Group by
```php
/* Simple */
$sql->group_by('field_name');

/* Descending resules */
$sql->group_by('field_name', false);

/* Ascending with rollup */
$sql->group_by('field_name', true, true);

/* Multiple fields */
$sql->group_by('field_name')->group_by('field_name');
```

#### Having
```php
/* Simple */
$sql->having(new sql_cond('some_field', sql::EQUALS, sql::q("some value")));

/* Written better */
$sql->having(
    new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
);

/* More complex */
$sql->having(
    new sql_and(
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_or(
            new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
            new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
        )
    )
);

/* With sub SQL */
$sql->having(
    new sql_and(
        new sql_cond('some_field', sql::EQUALS, $this->data_source->sql->select('*')->from('table')),
        new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
        new sql_or(
            new sql_cond('some_field', sql::EQUALS, sql::q("some value")),
            new sql_cond('some_field', sql::EQUALS, sql::q("some value"))
        )
    )
);
```

#### Order by
```php
/* Simple */
$sql->order_by('field_name');

/* Descending resules */
$sql->order_by('field_name', false);

/* Multiple fields */
$sql->order_by('field_name')->order_by('field_name');
```

#### Limit
```php
/* Limit to the first 200 */
$sql->limit(200);

/* limit to the second 200 */
$sql->limit(200, 2 * 200);
```

## Inserting
Inserting data is quite simple, you can do it record by record, or many records in one time.

```php
/* Simple */
$this
    ->data_source
    ->sql
    ->insert_into('car', array('name', 'date_created'))
    ->values(sql::q("Capri", new sql_now()))
    ->execute();

/* Multiple */
$this
    ->data_source
    ->sql
    ->insert_into('car', array('name', 'date_created'))
    ->values(sql::q("Capri", new sql_now()))
    ->values(sql::q("Escort", new sql_now()))
    ->values(sql::q("Focus", new sql_now()));
    ->execute();
```

If we need to get the id of the last record we can do this:
```php
$id = $this
    ->data_source
    ->sql
    ->insert_into('car', array('name', 'date_created'))
    ->values(sql::q("Capri", new sql_now()))
    ->execute()
    ->id();
```

Using arrays:
```php

$data = array(
    array(
        'name' => sql::q('Capri'),
        'date_created' => new sql_now()
    ),
    array(
        'name' => sql::q('Escort'),
        'date_created' => new sql_now()
    ),
    array(
        'name' => sql::q('Focus'),
        'date_created' => new sql_now()
    )
);

$this->data_source->sql
    ->insert_into('car', array_keys($data[0]))
    ->values($data);

```

Combining with select statements:
```php
$this->data_source->sql
    ->insert_into('car')
    ->select('*')
    ->from('some_table');
```

## Updating
Updates are just as simple as:
```php
$this->data_source
    ->sql
    ->update('table_name')
    ->set('field_name', sql::EQUALS, sql::q("value"))
    ->where(
        new sql_cond('field_name', sql::EQUALS, sql::q("value"))
    )
    ->execute();
```

You can update multiple tables by including them in an array:
```php
$this->data_source
    ->sql
    ->update(array('table 1', 'table 2'))
    ->set('field_name', sql::EQUALS, sql::q("value"))
    ->where(
        new sql_cond('field_name', sql::EQUALS, sql::q("value"))
    )
    ->execute();
```

And alias the tables using a hash array:
```php
$this->data_source
    ->sql
    ->update(array('table_1' => 'alias_1', 'table_2' => 'alias_2'))
    ->set('field_name', sql::EQUALS, sql::q("value"))
    ->where(
        new sql_cond('field_name', sql::EQUALS, sql::q("value"))
    )
    ->execute();
```
**IMPORTANT NOTE:** The `select()` function uses the hash array for aliasing the opposite way around to the `update()` function.  In a future release the `update()` function will be changed to work the same as the select.

## Deleting

As with the previous statements, deleting is just as easy.

```php
$this->data_source
    ->sql
    ->delete_from('table_1')
    ->where(
        new sql_cond('field_name', sql::EQUALS, sql::q("value"))
    )
    ->execute();
```

## Creating, dropping and altering
So far we have looked at the ways of manipulating data in the database so now lets look at how we alter the database itself.

For the most part this is relativily straight foward as you would expect, however creating or altering tables requires a little understanding of what Adapt is doing and the reason it's doing it.

Adapt is a bundle within a bundle management system (It also happens to be the bundle management system), every bundle in the system is versioned and every bundle can specify the versions of child bundles it requires to work.

Doing this allows bundles to create there own tables that are usable by other bundles inheriting from it.  For example, the user bundle depends on the session bundle, the session bundle creates a table called **session**, the user bundle uses this table because it knows the design of the table for a particular version of session.

What this means is that you can't change your schema design at runtime because bundles depending on yours could break.

So creating tables in Adapt is acheived by specifing them in your `bundle.xml` under the `schema` tag.

That said, there many be times when you need to create tables for whatever reason so keep reading to see how.

### Creating tables

The syntax is pretty simple, to create a table the syntax is:
```php
$this->data_source
    ->sql
    ->create_table('table')
    ->add('table_id', 'bigint')
    ->add('name', 'varchar(64)')
    ->primary_key('table')
    ->execute();
```
Although the syntax is correct, this statement will fail.  In order for the `model` and `sql` class to function correctly they need to understand the schema, for Adapt to load the schema it needs to know the bundle name of the class creating the table.

We can do this by setting the value of **adapt.installing_bundle** to our bundle name in the Adapt store before executing the statement.  It is important that we unset the value right execution.

This will work:
```php
$this->store('adapt.installing_bundle', "my_bundle_name");

$this->data_source
    ->sql
    ->create_table('table')
    ->add('table_id', 'bigint')
    ->add('name', 'varchar(64)')
    ->primary_key('table')
    ->execute();

$this->remove_store('adapt.installing_bundle');
```

