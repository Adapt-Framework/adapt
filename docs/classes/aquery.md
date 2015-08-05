# aquery
**Inherits from:** [base](/docs/classes/base.md)

`aquery` is jQuery for PHP allowing rapid manipulation of the DOM before it's sent to the client.



## Table of Contents

## Differences between jQuery and aQuery
The jQuery documentation is much more detailed than this, you can use the jQuery documentation as a reference but you should be aware of:
- aquery uses underscores instead of camel case.  So `addClass` in jQuery becomes `add_class` in aquery.
- jQuerys `empty()` function has been replaced with `clear()` as empty is a keyword in PHP.
- The following jQuery functions are not supported:
    - `wrapAll()`
    - `map()`
    - `replaceAll()`
    - `contents()`
    - `untilNext()`
    - `parentsUntil()`
    - `prevUntil()`

## Contstructing
### __construct(`$document = null`, `$selector = null`, `$parent = null`, `$root = null`)
Contructs a new `aquery` object.

#### Input:
- `$document = null` (Optional) Must be an instance of [html](/docs/classes/html.md) or a html string, by default `$document` is set to the DOM. Set this if you wish to query something that currently is attached to the DOM.
- `$selector = null` (Optional) If you would like to filter the results during contruction you can do so by specifing a selector here.
- `$parent = null` (Optional) If the element you are querying is not the root element you can optionally specify the parent element.
- `$root = null` (Optional) If the element you are querying is not the root element you can optionally specify the root element.

## Properties
### elements
Contains the current matched elements.  This can also be used to set the elements.

## Methods
### add_class(`$class_name`)
Adds a class to an element.

#### Input:
- `$class_name` The name of the class to add to the matched elements.

#### Returns:
- `self`

--

### css(`$property`, `$value = null`)
Set or gets a CSS property using the `style` attribute of the current matches elements.  When `$value` is null the current value is returned, when `$value` is set the property is updated.

#### Input:
- `$property` The CSS property that you'd like to get or set.
- `$value` (Optional) The value to be set.

#### Returns:
If `$value` is null the current value is returned, otherwise `null` is returned.

--

### remove_class(`$class_name`)
Removes a class from the current matched elements.

#### Input:
- `$class_name` The name of the class to be removed.

#### Returns:
- `self`

--

### has_class(`$class_name`)
Tests if the matched elements contains the class `$class_name`.  If multiple elements are matched all must contain the class name.

#### Input:
- `$class_name`

#### Returns:
- `true` or `false`

--

### toggle_class(`$class_name`, `$switch = null`)
When `$switch` is null toggle_class either adds or removes the class name depending on whether it is already present.  `$switch` can be `true` or `false`, when `true` toogle_class only add's the class, when `false` toggle_class only removes the class.

#### Input:
- `$class_name` The class name to toggle.
- `$switch` (Optional) Should we limit the toggle? (See above)

#### Returns:
- `self`

--

### text(`$text = null`)
Gets of set the text of the current matched elements.

#### Input:
- `$text` (Optional) A string to be set as a text node.

#### Returns:
- If `$text` is null the current value(s) of the matched elements are returned as a string.

--

### html(`$string = null`)
Sets or gets the child elements of the current matched set.

#### Input:
- `$html` (Optional) A string or instance of [html](/docs/classes/html.md)

#### Returns:
- If `$string` is null the current children of the matched elements are returned.

--

### val(`$value = null`)
Sets or get the `value` attribute from the current matched elements.  Where more than one element is matched only the first is used.

#### Input:
- `$value` (Optional) The new value to specify

#### Returns:
- When `$value` is null the current value of the attribute `value` is returned, otherwise `null` is returned.

--

### attr(`$attribute`, `$value = null`)
Gets or sets an attribute for the current matched set of elements.

#### Input:
- `$attribute` The attribute to get or set
- `$value` (Optional) The value to be set.

#### Returns:
- If `$value` is `null` then the current value of `$attribute` is returned, else `""` is returned.

--

### remove_attr(`$attribute`)
Removes an attribute from the current matched elements.

#### Input:
- `$attribute` The attribute to be removed.

#### Returns:
- `self`

--

### after(`$content`)
Inserts content after the current matched elements.

#### Input:
- `$content` must be an xml / html string, or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md)

#### Returns
- `self`

--

### append(`$content`)
Appends content to the current matched elements.

#### Input:
- `$content` must be an xml / html string, or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md)

#### Returns
- `self`

--

### before(`$content`)
Inserts content before the current matched elements.

#### Input:
- `$content` must be an xml / html string, or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md)

#### Returns
- `self`

--

### detach(`$selector = null`)
Detaches the current matched elements for the document, optionally filtered by `$selector`.

#### Input:
- `$selector` (Optional) A selector to filter the elements to be detached.

#### Returns
- A new `aquery` instance containing the detached element(s).

--

### clear()
Clears all current selections.  This is equivilent to jQuerys `empty()` but had to be renamed due to empty being a keyword in PHP.

#### Returns:
- `self`

--

### prepend(`$content`)
Prepends the `$content` to the current matched elements

#### Input:
- `$content` must be an xml / html string, or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md)

#### Returns
- `self`

--

### remove(`$selector = null`)
Removes the current matched elements from the document optionally filtered by `$selector`.

#### Input:
- `$selector` (Optional) The selector used to filter the elements to be removed.

#### Returns:
- `self`

--

### replace_with(`$content`)
Replaces the current matched elements with `$content`.

#### Input:
- `$content` The content that will replace the current selection.  This must be a string containing XML or HTML, or be an instance of [xml](/docs/classes/xml.md) or [html](/docs/classes/html.md).

#### Returns:
- `self`

--

### unwrap()
Removes the currently selected element(s) while append it's children to it's parent.

#### Returns:
- `self`

--

### wrap(`$wrapper`)
Detaches the current matched elements and wraps them inside `$wrapper` before appending the `$wrapper` to the document in the same place that the matched elements were found.

#### Input:
- `$wrapper` The new content to wrap the matched elements in.  This should be either a string containing XML / HTML or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md).

#### Returns:
- `self`

--

### wrap_inner(`$wrapper`)
Detaches the current matched elements and wraps them inside `$wrapper` before appending the `$wrapper` to the document in the same place that the matched elements were found.

#### Input:
- `$wrapper` The new content to wrap the matched elements in.  This should be either a string containing XML / HTML or an instance of [html](/docs/classes/html.md) or [xml](/docs/classes/xml.md).

#### Returns:
- `self`

--

### get(`$index = null`)
Returns all the matched elements as an `array()` or when `$index` is specified the matched element at the index `$index`.

#### Input:
- `$index` (Optional) The index of the item to be returned.  This maybe a negative number which will cause the list to act in reverse.  Setting `$index` to `1` will return the first element, setting `$index` to `-1` will return the last item.

#### Returns:
- `array()` of elements or a single [xml](/docs/classes/xml.md) / [html](/docs/classes/html.md) object.

--

### to_array()
Returns all the matched elements in an `array()`, this is the same as calling `get()` without an `$index`.

#### Returns:
- `array()`

--

### size()
Returns a count of the number of matched elements.

#### Returns:
- Integer

--

### eq(`$index`)
Filters the current matched elements to a single element at `$index`.

#### Input:
- `$index` A single number containing the index of the item you'd to continuing working with.

#### Returns:
- `self`

--

### filter(`$selector`)
Filters the current matched elements by `$selector`.

#### Input:
- `$selector` A CSS selector used to filter the current list.

#### Returns:
- A new instance of `aquery` containing the the matched and filtered elements.

--

### find(`$selector`)
Finds any elements matched by selector from the current position in the document.  This is the same as `filter()` but the same `aquery` object is returned instead of a new one.

#### Input:
- `$selector` A CSS selector used to filter the current list.

#### Returns:
- `self`

--

### first()
Filters the list of matched elements to just the first one.  This is the same as calling `$object->eq(0)`.

#### Returns:
- `self`

--

### has(`$selector`)
Reduces the number of matched elements filtered by `$selector` and returns a new `aquery` containing the filtered elements.

#### Input:
- `$selector` The selector to be used to filter the current matched set.

#### Returns:
- A new `aquery` containing the filtered elements.

--






