## Bundles

### What are bundles?

### Bundle structure
Each bundle will have a file structure something like this:
```
/
/bundle.xml
/boot.php
/install.php
/controllers/
/classes/
/models/
/views/
```

**bundle.xml** Contains a description about the bundle and any bundles it depends upon to function. This file is mandatory.
**install.php** Is called when the bundle if installed, typically this will build the bundles schema. This file is optional.
**boot.php** Is called when the framework boots, this allows you to carry out any tasks before the page has been built.
**/controllers/** Contains any view controllers you may have.
**/classes/** Any classes that are not controllers, models or views.
**/models/** Contains any models you have.
**/views/** Contains any view you have.

Other directories maybe used you are however responsible for auto loading them.

### bundle.xml
**bundle.xml** is a really simple way of describing your bundle and the version.  It also allows for dependencies to be defined which will automatically download and install upon this bundle being installed.

You can also use this file to declare any settings your bundle has, these settings can be set later by other bundles or the end user.

A typical bundle will have the following structure:
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

