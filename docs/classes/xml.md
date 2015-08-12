# Class `xml`
**Inherits from:** [`base`](/docs/class/base.md)

This class is used for reading or writing XML, this class represents a single node, nodes can be added to nodes to create a hierachy.

`xml` is also a registered handler class which means you can use the class prefix **xml_** to create new classes on the fly.

## Table of contents

## Examples of usage
### Wrting XML

In this example we will create the following XML structure:
```xml
<adapt_framework>
    <bundle>
        <name>adapt</name>
    </bundle
</adapt_framework>
```

```php
/*
 * Without using handlers
 */
$xml = new xml(
    'adapt_framework',
    new xml(
        'bundle',
        new xml(
            'name',
            'adapt'
        )
    )
);

/* Print out the result */
print $xml;


/*
 * Same example using class handlers
 */
$xml = new xml_adapt_framework(new xml_bundle(new xml_name('adapt')));
print $xml;

/*
 * Same example using handlers and the 'add' method
 */
$xml = new xml_adapt_framework();
$bundle = new xml_bundle();
$name = new xml_name('adapt');

/* Add name to bundle */
$bundle->add($name);

/* Add bundle to adapt_framework */
$xml->add($bundle);

/* Print out the result */
print $xml;
```

### Reading XML

In this example we will read the XML output produced from the above example.

```php
/* Create a variable to hold our XML */
$raw_xml = "<adapt_framework><bundle><name>adapt</name></bundle></adapt_framework>";

/* Check if the XML is valid */
if (xml::is_xml($raw_xml)){
    /* Parse the XML */
    $xml = xml::parse($raw_xml);
}
```

You can also mix XML objects and XML strings, in the next example we will build the outer `<adapt_framework></adapt_framework>` and then create the rest with text nodes.
```php
/* Create the outer adapt_framework node */
$xml = new xml_adapt_framework();

/* Create a string defining the rest of the XML structure */
$raw_xml = "<bundle><name>adapt</name></bundle>";

/* Add the string to the object */
$xml->add($raw_xml);
```

In the above example `$raw_xml` is automatically parsed the moment it is added to an `xml` instance.  There may be times when you would want to add raw xml but not parse it, there are two ways to do this:

**1.** You can escape the raw xml before adding it by:
```php
$raw_xml = xml::escpae("<bundle><name>adapt</name></bundle>");
$xml->add($raw_xml);
```

**2.** Or you can use the `_add` method which takes two params, the first is the object or string to add, the second is a boolean indicating if the first param should be parsed:
```php
$raw_xml = "<bundle><name>adapt</name></bundle>";
$xml->_add($raw_xml, false);
```