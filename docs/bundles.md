# Bundles

## What are bundles?
In Adapt everything is a bundle, Adapt is even a bundle.  A bundle is just a bunch of useful code that does something.  A bundle could be a framework like Adapt, it could be locality information, or a form libaray or even a fully blown web application.  The idea is simple, you break code in to small independent useful chucks of code, bundle it up with a name and a version and then include it in future work.

Bundles can depend on other bundles, so instead of writing large applications, you write a smaller simpler bundle that pulls in functionality from other bundles.  Take for example the **Administrator** bundle, this provides a rich website administration tool, featuring platform and user management, including this means you only need to write that which is custom to your application. **Administrator** of course has lots of dependiences, which in turn have more.

By building this way we can solve problems truly once.

To include **Administrator** in your application you need only reference it, Adapt will download and install it the next time your application runs.

Outside of the framework is the Adapt repository, a centralised place where bundles are stored.  The repository is open to anyone and anyone can publish bundles, of course you have the option of making them public or private.

By integrating the repository into the fabric of the framework we can automatically install, update, upgrade and self heal when files go missing.  When you write applications with Adapt then you also get the ability to deploy on demand.

Because bundles are versioned and Adapt has been designed to handle updates it becomes possible to provide continuous deployment for any application you write.

## Bundle file structure
Bundles have a typical file structure as indicated below, this however is not set in stone and bundles may include whatever they wish.
```
/
/bundle.xml
/controllers    <Directory>
/classes        <Directory>
/models         <Directory>
/views          <Directory>
/static         <Directory>
```

The directories for models, views and controllers hold as expected, models, views and controllers. The classes directory is for any classes that are not models, views or controllers.  The static directory holds any static content your bundle needs.

## bundle.xml
This file tells Adapt about your bundle.  This file contains meta data such as the bundles name and version, a label and description for display in the repository should you publish it.  This file is also used to define the data schema used by your bundle and any dependencies your bundle has.

A typical bundle.xml will have the following structure:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<adapt_framework>
    <bundle>
        <label>My First Web App</label>
        <name>my_app</name>
        <version>1.0.0</version>
        <type>application</type>
        <namespace>\my_namespace</namespace>
        <description>Test application</description>
    </bundle>
</adapt_framework>
```

### Available bundle elements
Name            | Required      | Description
----------------|---------------|----------------
name            | Always        | The name of the bundle. This should be all lowercase alpha chars or underscores. If publishing to Adapt repository then the name must also be unique.
version         | Always        | This is the version of the bundle, this should be in the form of xx.xx.xx following the rules of [http://semver.org/](http://semver.org/). When publishing to the repository you may re-publish at anytime but the version must be different from all previous published versions.
label           | Always        | A human friendly label describing the bundle.  When publishing to the repository the label from the most recent version will be used to the listing.
description    | Only when posting to the public repository | A rich description of the bundle using only plan text.
type           | Always         | This tells Adapt what your bundle is used for, you can obtain a list of valid types from [https://repository.adaptframework.com/v1/bundle-types](https://repository.adaptframework.com/v1/bundle-types), when building an application it must always be **application**
namespace       | Always        | The namespace being used by the bundle, for example, **\adapt**.  The namespace here must be used for all models, views and controllers.  You should also use it for all other classes.  You can include more namespaces when your bundle is booting, see bundle control below.
version_status | When posting to Adapt repository | Can be one of **alpha**, **beta**, **release_candidate**, **release**. By allowing development versions in the repository we can offer continious build testing to dev teams.
availability   | When posting to Adapt repository | Can be one of **public** of **private**.  **Important note:** Bundles pushed to the repository with a status of **public** may not be withdrawn at a later date.  Because the nature of Adapt is building small blocks, pulling a small one used in many web applications would break alot of things.  If you tell us its **public**, it's public
author         | Optional       | Provides information about the author.

### authors
To provide information about the authors of a bundle you can use:
```xml
<authors>
    <author>
        <name>Matt Bruton</name>
        <email>matt.bruton@gmail.com</email>
    </author>
</authors>
```

### Contributers
```xml
<contributors>
    <contributor>
        <name>Joe Hockaday</name>
        <email>jdhockad@hotmail.com</email>
    </contributor>
    <contributor>
        <name>Sion Purnell</name>
        <email>yepitsmesion@hotmail.co.uk</email>
    </contributor>
</contributors>
```

### Vendor
To provide information about the bundles vendors use:
```xml
<vendor>
    <name>Adapt Framework</name>
    <website>https://www.adaptframework.com</website>
</vendor>
```

### depends_on
Use this tag to tell adapt about any dependencies you require.  You must always provide the **name** element, the **version** element is optional.

When specifing a version it should always be in the format **X** or **X.X**, you shoudn't provide a revision.

For example, to include jQuery and Adapt Administrator:
```xml
<depends_on>
    <bundle>
        <name>jquery</name>
    </bundle>
    <bundle>
        <name>administrator</name>
        <version>1</version>
    </bundle>
</depends_on>
```

### settings
Your bundle may wish to include settings, Adapt supports a key/value system.

You can define settings like so:
```xml
<settings>
    <category name="XML &amp; HTML Settings">
      <setting>
        <name>xml.readable</name>
        <label>Output readable XML/HTML?</label>
        <default_value>No</default_value>
        <allowed_values>
          <value>Yes</value>
          <value>No</value>
        </allowed_values>
      </setting>
      <setting>
        <name>html.format</name>
        <label>HTML Format</label>
        <default_value>html5</default_value>
        <allowed_values>
          <value>html5</value>
          <value>xhtml</value>
        </allowed_values>
      </setting>
      <setting>
        <name>html.closed_tags</name>
        <label>Closed HTML tags</label>
        <default_values>
          <value>img</value>
          <value>link</value>
          <value>meta</value>
          <value>br</value>
          <value>hr</value>
          <value>area</value>
          <value>base</value>
          <value>col</value>
          <value>command</value>
          <value>embed</value>
          <value>input</value>
          <value>link</value>
          <value>meta</value>
          <value>param</value>
          <value>source</value>
        </default_values>
      </setting>
    </category>
</settings>
```

**allowed_values** is optional.  Note that **default_value** is used to default a single value, where as **default_values** allows an array to be specified.

The values of settings can be accessed at runtime from within any class like so:
```php
$value = $this->setting('some.setting.name');
```

You can also set the value at runtime, please note that this only remains for the duration of the current request. To override the setting completely enter the new value in **settings.xml** file.
```php
// Only set for the remainder of the current request
$this->setting('some.setting.name', 'new value');
``

### schema

### Custom tag handling

## Bundle control
