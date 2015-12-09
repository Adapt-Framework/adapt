# Class `data_source_mysql`

**Inherits from:** [`xml`](/docs/classes/xml.md) > [`base`](/docs/classes/base.md)

This class is used for reading or writing HTML, this class represents a single node, nodes can be added to nodes to create a hierachy.

`html` is also a registered handler class which means you can use the class prefix **html_** to create new classes on the fly.

## Table of contents

## Contructing
### __construct(`$tag = null`, `$data = null`, `$attributes = array()`)
Construct a new HTML node.

#### Input:
- `$tag` The HTML node name
- `$data` (Optional) Any data or child nodes you would like to add.
- `$attributes` (Optional) As associative array of attribute / value pairs

## Properties
### id (R/W)
Gets or sets the HTML id attribute.

## Methods
### set_id(`$id = null`)
Sets the id attribute to the value of `$id`, when `$id` is null Adapt automatically sets a unqiue id.

#### Inputs:
- `$id` (Optional) The value to set the HTML id attribute to.

--

### add_class(`$class`)
Adds the `$class` to the class attribute of this node.

#### Inputs:
- `$class` The name of the class to add to this node.

--

### remove_class(`$class`)
Remove the named `$class` from this node.

#### Inputs:
- `$class` The name of the class to be removed from this node.

--


