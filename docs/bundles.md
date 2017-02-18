# Bundles

## What are bundles?
In Adapt everything is a bundle, Adapt is even a bundle.  A bundle is just a bunch of useful code that does something.  A bundle could be a framework like Adapt, it could be locality information, or a form libaray or even a fully blown web application.  The idea is simple, you break code in to small independent useful chucks, bundle it up with a name and a version and then include it in future work.

Bundles can depend on other bundles, so instead of writing large applications, you write a smimple bundle that pulls in functionality from other bundles.  Take for example the **Administrator** bundle, this provides a rich website administration tool, featuring platform and user management, including this means you only need to write that which is custom to your application. **Administrator** of course has lots of dependiences, which in turn have more.

By building this way we can solve problems truly once.

To include **Administrator** in your application you need only reference it, Adapt will download and install it the next time your application runs.  Outside of the framework is the Adapt repository, a centralised place where bundles are stored.  The repository is open to anyone and anyone can publish to bundles, of course you have the option of making them public or private.

By intergrating the repository into the fabric of the framework we can automatically install, update, upgrade and self heal when files go missing.  When you write applications with Adapt then you also get the ability to deploy on demand.

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
        <namespace>/applications/my_app</namespace>
        <description>Test application</description>
    </bundle>
</adapt_framework>
```

### Available bundle elements
Name            | Required      | Description
----------------+---------------+----------------
name            | Always        | The name of the bundle. This should be all lowercase alpha chars or underscores. If publishing to Adapt repository then the name must also be unique.
version         | Always        | This is the version of the bundle, this should be in the form of xx.xx.xx following the rules of [http://semver.org/](http://semver.org/). When publishing to the repository you may re-publish at anytime but the version must be different from all previous published versions.
label           | Always        | A human friendly label describing the bundle.  When publishing to the repository the label from the most recent version will be used to the listing.
description    | Only when posting to the public repository | A rich description of the bundle using only plan text.
type           | Always         | 

#### Available bundle elements
##### label
This is how you would like the name of your bundle to appear in the respository.

#### name
This is the bundle name, this needs to be unqiue and contain only alpha chars and underscores.  The bundle will be rejected from the repository if the name is already in use.

#### description
A rich description that will appear next to your bundle in respository.

#### version
This is the version of the bundle, this should be in the form of xx.xx.xx following the rules of [http://semver.org/](http://semver.org/).

#### type
This defines the type of bundle, think of this as a category that can be used to search for bundles.
- **application** These are web apps that can be installed on a web server.  All applications must use this type.
- **data_source_driver** Use this if your bundle provides additional database functionality.
- **locale** Use this if you are extending the locales bundle to provide additional locales information.
- **data_type** Use this if you are providing additional data types.
- **form_field** Use this if you are extending the form bundle to provide additional form fields.
- **extension** These are bundles that provide functionallity.
- **frameworks** These are bundles that provide the foundations for other bundles, **Adapt** is a framework.
- **templates** These are not currently available.

