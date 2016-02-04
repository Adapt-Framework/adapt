# Getting started with Adapt

This article assumes you have already installed Adapt, if you haven't please see [Installing Adapt](/docs/articles/installing_adapt.md).

## Basic directory structure
After installing Adapt and choosing to develop a Web Application, assuming you called your application **first_web_application** you will have
a directory structure like this in your root web directory.

```
index.php
adapt/
    settings.xml
    adapt/
    adapt_setup/
    bootstrap/
    bootstrap_model_manager/
    bootstrap_views/
    first_web_application/
    font_awesome/
    font_awesome_views/
    jquery/
    jquery_ui/
    store/
```

Lets look at each one in a little more detail:

* ```index.php``` This is the first file called for each web request, it's job is to start Adapt.  You should never need to change anything in this file.
* ```adapt/``` Is a directory containing all the code and assets.
* ```adapt/settings.xml``` Contains global settings, if you created a database connection during install it will be saved here.
* ```adapt/adapt/``` This contains the Adapt framework bundle, all the code in here is framework code.
* ```adapt/adapt_setup/``` This contains a web application bundle called 'Adapt Setup'.  This application bundle was installed during installation and provided an interface to setup a database connection.
* ```adapt/bootstrap/``` Contains the popular Bootstrap CSS framework.  This bundle was installed by the adapt_setup bundle.  See [GetBootstrap.com](http://getbootstrap.com) for more.
* ```adapt/bootstrap_model_manager/``` A bundle that extends ```adapt/bootstrap/``` to allow multiple modals to exist at the sametime.  This bundle was installed by adapt_setp bundle.
* ```adapt/bootstrap_views/``` A bundle that provides easy to use views of all the Bootstrap componets.  The bundle was installed by the adapt_setup bundle.
* ```adapt/font_awesome/``` A bundle containing all the awesomeness that is Font Awesome. The bundle was installed by the adapt_setup bundle. See [FontAwesome.io](http://fontawesome.io) for more.
* ```adapt/font_awesome_views/``` A bundle containing easy to use views for use with Font Awesome.  This bundle was installed by adapt_setup bundle.
* ```adapt/jquery/``` A bundle containing jQuery. The bundle was installed by the adapt_setup bundle. See [jQuery.com](http://jquery.com) for more.
* ```adapt/jquery_ui/``` A bundle containing jQuery UI. The bundle was installed by the adapt_setup bundle. See [jQueryUI.com](http://jqueryui.com) for more.
* ```adapt/store/``` Is used to store data from any bundle.  See [File Storage System](/docs/articles/file_storage_system.md) for more.
* ```adapt/first_web_application/``` This directory holds everything required for our application. All the files you change are in here.

Each of the above bundle directories will contain sub directories with the bundle name and version, for example, the adapt_setup bundle folder will contain a sub folder named ```adapt_setup-X.X.X``` with X indicating the version.

Your first web application will also include a sub folder containing the name and version ```adapt/first_web_application/first_web_application-1.0.0```, this directory we will refer too as you ***application bundle directory***.


## Bundle structure

Everything in Adapt is a bundle, this means if a bundle exists that provides the functionaliy you need then you can just use it.  It also means any bundles you write can be re-used in any project in the future.

The web application you created earlier will contain the following, the same is true of every bundle.

```
first_web_application-1.0.0/
    bundle.xml
    classes/
        bundle_first_web_application.php
    controllers/
        controller_root.php
    docs/
    models/
    static/
        css/
        js/
        images/
    views/
```

* ```first_web_application-1.0.0/bundle.xml``` Contains key information about the bundle, see the sub heading below labled bundle.xml. 
* ```first_web_application-1.0.0/classes/``` Contains classes for this bundle. 
* ```first_web_application-1.0.0/classes/bundle_first_web_application.php``` Contains the bundles boot process and performs any actions required during installation.  See [Working with bundles](/docs/articles/working_with_bundles.md) for more information.
* ```first_web_application-1.0.0/controllers/``` Contains the view controllers for this bundle.
* ```first_web_application-1.0.0/controllers/controller_root.php``` The main view controller for the application.  This controller is responsible for URL routing for your site.
* ```first_web_application-1.0.0/docs/``` The documentation for the bundle.
* ```first_web_application-1.0.0/models/``` Contains the models for this bundle.
* ```first_web_application-1.0.0/static/``` Contains any static content this bundle uses.
* ```first_web_application-1.0.0/views/``` Contains the views for this bundle.

### bundle.xml
`bundle.xml` is the heart of the bundle, containing key information about the bundle. You can use this file to define settings, database tables and list any bundles that this bundle requires to work.

The basic `bundle.xml` looks something like this:

```xml
<?xml version="1.0" encoding="utf-8"?>
<adapt_framework>
    <bundle>
        <name>first_web_application</name>
        <version>1.0.0</version>
        <label>My First Web App</label>
        <namespace>\first_web_application</namespace>
        <type>application</type>
        <description>My first web application!</description>
    </bundle>
</adapt_framework>
```

The key elements of the bundle are as follows:

Element         | Description
----------------|-----------------
**name**        | The name of the bundle. If this bundle is published in the Adapt repository then the name must be unique.
**version**     | The version of the bundle.  This must always be in the format X.X.X

