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

```index.php``` This is the first file called for each web request, it's job is to start Adapt.  You should never need to change anything in this file.

```adapt/``` Is a directory containing all the code and assets.

```adapt/settings.xml``` Contains global settings, if you created a database connection during install it will be saved here.

```adapt/adapt/``` This contains the Adapt framework bundle, all the code in here is framework code.

```adapt/adapt_setup/``` This contains a web application bundle called 'Adapt Setup'.  This application bundle was installed during installation and provided an interface to setup a database connection.

```adapt/bootstrap/``` Contains the popular Bootstrap CSS framework.  This bundle was installed by the adapt_setup bundle.  See [GetBootstrap.com](http://getbootstrap.com) for more.

```adapt/bootstrap_model_manager/``` A bundle that extends ```adapt/bootstrap/``` to allow multiple modals to exist at the sametime.  This bundle was installed by adapt_setp bundle.

```adapt/bootstrap_views/``` A bundle that provides easy to use views of all the Bootstrap componets.  The bundle was installed by the adapt_setup bundle.

```adapt/font_awesome/``` A bundle containing all the awesomeness that is Font Awesome. The bundle was installed by the adapt_setup bundle. See [FontAwesome.io](http://fontawesome.io) for more.

```adapt/font_awesome_views/``` A bundle containing easy to use views for use with Font Awesome.  This bundle was installed by adapt_setup bundle.

```adapt/jquery/``` A bundle containing jQuery. The bundle was installed by the adapt_setup bundle. See [jQuery.com](http://jquery.com) for more.

```adapt/jquery_ui/``` A bundle containing jQuery UI. The bundle was installed by the adapt_setup bundle. See [jQueryUI.com](http://jqueryui.com) for more.

```adapt/store/``` Is used to store data from any bundle.  See [File Storage System](/docs/articles/file_storage_system.md) for more.

```adapt/first_web_application/``` This directory holds everything required for our application. All the files you change are in here.

Each of the above bundle directories will contain sub directories with the bundle name and version, for example, the adapt_setup bundle folder will contain a sub folder named ```adapt_setup-X.X.X``` with X indicating the version.



