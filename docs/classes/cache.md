# Class cache

**Inherits from:** [`base`](/docs/classes/base.md)

Caches views, SQL statements, pages, javascript, css or any other type of data.  For a more indepth understanding of caching please read [understaning caching in adapt](/docs/caching.md)

## Table of contents
- [Methods](#methods)
    - [get](#getkey)
    - [get_content_type](#get_content_typekey)
    - [set](#setkey-data-expires--300-content_type--null-public--false)
    - [delete](#deletekey)
    - [sql](#sqlkey-sql_results--array-expires--null)
    - [view](#viewkey-view-expires--null)
    - [page](#pagekey-html-expires--null)
    - [serialize](#serializekey-object-expires--300)
    - [javascript](#javascriptkey-javascript-expires--600-public--false)
    - [css](#csskey-css-expires--600-public--false)

## Methods

### get(`$key`)
Returns the cached data for the specified key.

#### Input:
- `$key` A unique key identifing the cached data.

#### Returns:
- Mixed depending on the data stored. If the data is unavailable or expired `null` is returned.

--

### get_content_type(`$key`)
Returns the content type of the data held agaisnt a particular key.

#### Inputs:
- `$key` A unqiue key indentifing the cached data.

#### Returns:
- `string` containing the mime type.

--

### set(`$key`, `$data`, `$expires = 300`, `$content_type = null`, `$public = false`)
Caches data using a unqiue key for a set period of time.

#### Inputs:
- `$key` A unique key idetifying the data you would like to cache.
- `$data` The data you would like cache, this can be anything.
- `$expires` (Optional) The number of seconds the data should be cached for.
- `$content_type` (Optional) The mime type of the data being store.  For PHP objects please use **applicaiton/octet-stream**.
- `$public` (Optional) Boolean indicating if the data should be publically accessible?  This is useful if you minify CSS or JS on the fly or are caching images.

--

### delete(`$key`)
Deletes data from the cache.

#### Inputs:
- `$key` A unique key identifing the data you'd like to delete from the cache.

--

### sql(`$key`, `$sql_results = array()`, `$expires = null`)
Cache a SQL result.

#### Input:
- `$key` A unique key identifing the data to be cached, for SQL statements this should be the statement itself.
- `$sql_results` An array containing the result of the SQL statement.
- `$expires` (Optional) How many seconds should the data be cached for?  When `null` the value of the setting **adapt.sql_cache_expires_after** is used.

--

### view(`$key`, `$view`, `$expires = null`)
Caches a view.

#### Input:
- `$key` A unique key representing the view to be cached.
- `$view` The view to be cached.
- `$expires` (Optional) The number of seconds to cache the view for.  When `null` the value of the setting **adapt.view_cache_exires_after** is used.

--

### page(`$key`, `$html`, `$expires = null`)
Cache a page.

#### Inputs:
- `$key` A unique key identifing the page to be cached.  This should always be the URL.
- `$html` This should either raw HTML or be an instance of [`page`](/docs/views/page.md).
- `$expires` (Optional) The number of seconds to cache this page for.  When `null` the value of the setting **adapt.page_cahce_expires_after** is used.

--

### serialize(`$key`, `$object`, `$expires = 300`)
Cache any serializable object or array.

#### Inputs:
- `$key` A unique key identifing the object to be cached.
- `$object` A serilizable PHP object or an array.
- `$expires` How many seconds should this be cahced for?  The default is 300 seconds (5 minutes).

--

### javascript(`$key`, `$javascript`, `$expires = 600`, `$public = false`)
Caches javascript.

#### Inputs:
- `$key` A unique key identifing the javascript to be cached.
- `$javascript` The javascript to be cached.
- `$expires` (Optional) How many seconds should the javascript be cahced for?  The default is 600 seconds (10 minutes)
- `$public` (Optional) Should the cahced javascript be made publically available?

--

### css(`$key`, `$css`, `$expires = 600`, `$public = false`)
Caches CSS.

#### Inputs:
- `$key` A unique key identifing the CSS to be cached.
- `$css` The CSS to be cached.
- `$expires` (Optional) How many seconds should the CSS be cahced for?  The default is 600 seconds (10 minutes)
- `$public` (Optional) Should the cahced CSS be made publically available?

--