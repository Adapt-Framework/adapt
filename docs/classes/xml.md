# Class `xml`
**Inherits from:** [`base`](/docs/class/base.md)

This class is used for reading or writing XML, this class represents a single node, nodes can be added to nodes to create a hierachy.

`xml` is also a registered handler class which means you can use the class prefix **xml_** to create new classes on the fly.

## Table of contents

## Examples of usage
### Writing XML

In this example we will create the following XML structure:
```xml
<adapt_framework>
    <bundle>
        <name>adapt</name>
    </bundle>
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

## Constructing
### __construct(`$tag = null`, `$data = null`, `$attributes = null`, `$closing_tag = false`)
Construct a new XML node.

#### Input:
- `$tag` The XML node name
- `$data` (Optional) Any data or child nodes you would like to add.
- `$attributes` (Optional) As associative array of attribute / value pairs
- `$closing_tag` (Optional) Should empty tags include the closing tag like `<tag_name></tag_name>` or should it be omitted such as `<tag_name />`.

## Constants
Name                | Value
--------------------|-----------------------
EVENT_RENDERED      | 'adapt.rendered'
EVENT_CHILD_ADDED   | 'adapt.child_added'
EVENT_CHILD_REMOVED | 'adapt.child_removed'

## Events
### EVENT_RENDERED
Is fired when the node is rendered to text.

#### Example:
```php
/* Create a node */
$xml = new xml_node();

/* Add a new event listener */
$xml->on(
    xml::EVENT_RENDERED,
    function($event){
        /* $event is an array containing the event data */
    }
);

/* Trigger the event */
print $xml_node;
```

### EVENT_CHILD_ADDED
Is fired when a child is added to this node.  The child could be text, a number or another instance of `xml`.

### EVENT_CHILD_REMOVED
Is fired when a child is removed from this node.

## Properties
### tag (R/W)
### namesapce (R/W)
### parent (R/W)
### text (RO)
### attributes (RO)

## Magic methods
### __toString()
`xml` uses the __toString() magic method to render the node.  This allows you to print or echo any instance of `xml` as if it were a string.

## Methods
### find(`$selector = null`)
Returns a new [`aquery`](/docs/classes/aquery.md) object which starts at this node and is optionally filtered by a selector.

#### Input:
- `$selector` (Optional) A CSS selector used to filter the results

#### Returns:
- [`aquery`](/docs/classes/aquery.md)

#### Example:
```php
/* Create a simple XML structure */
$raw_xml = "<items><item>1</item><item>2</item><item>3</item></items>";

/* Convert to an instance of xml */
$xml = xml::parse($raw_xml);

/* Using aquery, find and remove the first item */
$xml->find('item')->first()->detach();

/* Printout the new XML */
print $xml;
```

--

### add(`$item1`, `$item2`, ...)
Add one or more items to this node.

#### Input:
This method doesn't have a fixed number of params, you can can add multiple items by suppling them as seperate params or adding a single param containing an array of items.

#### Example:
This example shows three ways to add items
```php
/* Create a node */
$xml = new xml_node();

/* Add a new child */
$xml->add(new xml_child_node());

/* Add multiple children without an array */
$xml->add('item1', new xml_item_2(), new xml_item_3());

/* Add multiple children using an array */
$items = array(new xml_item_1(), 'Item 2');
$xml->add($items);
```

--

### _add(`$child`, `$parse = true`)
This method is final and cannot be overridden.  This method is used internally by adapt, it can be useful if you wish to add items without them being parsed.

#### Input:
- `$child` Instance of `xml` or a string.
- `$parse` (Optional) A boolean indicating if text nodes should be parsed, the default is true.

--

### get(`$index = null`)
Returns the child at index `$index` or when `$index` is `null` an array containing all children.

#### Input:
- `$index` (Optional) An integer representing the child node you would like to get.

#### Returns:
- Either
    - `array` Containing all children
    - A single item, could be a string, a number or an instance of `xml`

--

### set(`$index`, `$item`)
Set a child at the index specified.

#### Input:
- `$index` The index of the child to be replaced.
- `$item` The item to store at the specified index.

--

### remove(`$index_or_child = null`)
Removes a child from this node.  When `$index_or_child` is `null` all children are removed.

#### Input:
- `$index_or_child` (Optional) The index of the child to remove, or the child itself.

#### Returns:
- `true` if the child was successfully removed.
- `false` if the child could not be removed, or the child didn't exist.

--

### clear()
Removes all child objects from this node.

--

### count()
Returns a count of the children this node has.

--

### value(`$value = null`)
Sets or returns the text value of this node.

#### Input:
- `$value` (Optional) Set the value of the node.

#### Returns:
- The value of the node

--

### attr(`$key`, `$value = null`)
Set or get an attribute for this node.

#### Inputs:
- `$key` The attribute name to get or set
- `$value` (Optional) When provided the method acts as a setter, when `null` this method becomes a getter.


#### Returns:
- Either:
    - A string or number when acting as a getter
    - `null` when acting as a getter

--

### attribute(`$key`, `$value = null`)
Alias of [`attr()`](#attrkey-value--null).

--

### remove_attr(`$key`)
Removes the attribute named `$key` from this node if it exists.

#### Input:
- `$key` The name of the attribute to be removed.

--

### remove_attribute(`$key`)
Alias of [`remove_attr`](#remove_attrkey).

--

### has_attr(`$key`)
Checks if this node has the attribute named `$key`.

#### Input:
- `$key` The name of the attribute to check.

#### Returns:
- `true` if the attribute exists.
- `false` if it doesn't.

--

### has_attribute(`$key`)
Alias of [`has_attr`](#has_attrkey).

--

### render_attribute(`$key`, `$value`)
Returns a string containing a single HTML attribute and assocated value.

#### Input:
- `$key` The attribute name.
- `$value` The attribute value.

#### Returns:
- `KEY="VALUE"`

--

### render(`$close_all_empty_tags = false`, `$add_slash_to_empty_tags = true`, `$depth = 0`)
Renders this node and returns an XML string representation of the node.
For the most part you can use this method without providing any of the parameters `render()`.  The first two params are used to change the style of XML, the last is used internal by Adapt and can be missed.

When the setting `xml.readable` is set to `Yes` this method will output XML in a human readable form.  By default this setting is set to `No`.

#### Input:
- `$close_all_empty_tags` (Optional) Should all empty nodes have a `</node_name>` added.  This is only applicable when constructed with `$closing_tag = false`.
- `$add_slash_to_empty_tags` (Optional) Should empty nodes be expressed as `<node_name />`. This is only applicable when constructed with `$closing_tag = false`.
- `$depth` (Optional) When outputing XML in human readable form, `$depth` would be the number of spaces to be inserted before the opening tag.  Adapt will handle this automatically.

#### Returns:
- A XML string representation of this node.


## Static Methods
### escape(`$string`)
Returns an XML escaped string.

#### Input:
- `$string` The string to be escaped.

#### Returns:
- An XML escaped string.

--

### Unescape(`$string`)
Take an XML escaped string and unescapes it.

#### Inputs:
- `$string` An XML escaped string

#### Returns:
- A string

--

### parse(`$data`, `$return_as_document = false`, `$alternative_first_node_object = null`)
Takes an XML string as `$data` and returns an `xml` object.

#### Input:
- `$data` An XML string.
- `$return_as_document` (Optional) When `true` the method returns a [`xml_document`](/docs/classes/xml_document.md) object instead of an `xml` object.
- `$alternative_first_node_object` (Optional) When provided the method return this object instead of an `xml` object.  `$alternative_first_node_object` must be an instance of `xml` (via inheritance) and when provided overrides `$return_as_document`.

--

### is_xml(`$string`)
Determins if a string is likely XML or not.  This method **does not** validate the XML.

#### Input:
- `$string` The string to be checked.

#### Returns:
- `true` if it *looks* like XML
- `false` if it isn't XML

