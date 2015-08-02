# Adapt PHP Framework
Adapt is a framework designed for rapid web application development and deployment. By design Adapt can scale so you can focus on the core of your application. 

## Installing
To install adapt simply put [install.php](https://github.com/mbruton/adapt_installer) in the root folder of your webserver, no other files should be present as they will be unavailable after install.  Then view it in a web browser, unless there are issues the installer will complete automatically.

The installer will install the framework and a web app called [adapt_setup](https://github.com/mbruton/adapt_installer), adapt_setup will prompt you to create a database connection which you will need to build applications with.

The next step of the setup doesn't work, this is still in development so it's manual from here on out.

Delete the directory **DOCUMENT_ROOT/adapt/applications/adapt_setup** as this is the application that is currently running.

## Building your first application
We need to then create a new directory called **my_app** in **DOCUMENT_ROOT/adapt/applications**, this will be where our application is to be built.

We need to create a new file called **bundle.xml** in **DOCUMENT_ROOT/adapt/applications/my_app/bundle.xml**, this tells adapt about your application and anything that your application needs to function.

Your bundle.xml file should read like this:
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

This file has two things to note, firstly the name **my_app** must match the directory name you created earlier, and the namespace must always be **/applications/APP_NAME**, failing to do this cause the boot process to fail for your application.

Also note that all classes you create that access the framework must be declared in the above named namespace, failure to do so will render the class unavailable.  This may seem rigid, however it allows us to do the next bit...

### Making life easy ;)
So lets say your application needs jQuery, easy! Before the closing bundle tag in bundle.xml add the following
```xml
        <depends_on>
            <bundle>jquery</bundle>
        </depends_on>
```

So what if you wanted jQuery and Bootstrap? It's, a simple as
```xml
        <depends_on>
            <bundle>jquery</bundle>
            <bundle>bootstrap</bundle>
        </depends_on>
```

Giving you a **bundle.xml** that looks like this:
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
        <depends_on>
            <bundle>jquery</bundle>
            <bundle>bootstrap</bundle>
        </depends_on>
    </bundle>
</adapt_framework>
```

Also go ahead and add **bootstrap_views** and **font_awesome_views** giving you a final bundle.xml that looks like this:

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
        <depends_on>
            <bundle>jquery</bundle>
            <bundle>bootstrap</bundle>
            <bundle>bootstrap_views</bundle>
            <bundle>font_awesome_views</bundle>
        </depends_on>
    </bundle>
</adapt_framework>
```

### So how does that work?
When your application boots for the first time the dependecies will automatically be downloaded and installed from the adapt repository.

#### So what else is available?
Ok  it's really really important to note at this stage that Adapt is in beta and most of the "Bundles" are still in development.

##### administrator
Provides an administration section for your web app, accessed via www.example.com/administrator

##### advanced_data_types

##### bootstrap_views
Provides views for rapidly deploying bootstrap components

##### contacts
Provides base functionality for storing people data from any country

##### email
Provides SMTP, IMAP and MIME support for working with email with ease :)

##### font_awesome
Provides FontAwesome and it's awesome icon set

##### font_awesome_views
Provides views to easily add icons, stacked or even animated to the page.

##### form_datetime_picker
Extends the forms bundle and adds various date and time pickers and formats

##### form_password_confirm
Extends the forms bundle and add a password confirmation field with password strength indicator.

##### forms
Provides rich and complex forms that validate client and server side.  Forms can be multi-paged, with or without step indicators with sections that can repeat when you need to repeat the capture of data.  Not stopping there forms provides realtime feedback and fields, sections and pages can be dependent on other fields being certain values.

Still more, forms can easily be extended and new fuctionality provided.

##### form_text_editor
Provides a WYSIWYG html editor for the forms bundle.

##### locales
Provides the foundation for locality information.

##### locales_uk
Extends the locales bundle to provide validators and formatters for UK date, times, phone numbers, address formats and post codes.

##### locales_us
Extends the locales bundle to provide validators and formatters for US date, times, phone numbers, address formats and zip codes.

##### menus
Allows the creation of complex menus such as navgation bars or tabs.  This bundle can easily be extended to provide new formats.

##### minifier
Minifies all the css and javascript, packs them into two files (css & js), caches the result and outputs two minified files saving http requests and lowering bandwidth.  Unless you have a lot of js & css file or you need to be quick for mobiles you shouldn't use this as it causes each request to be slightly slower.

##### roles_and_permissions
Adds complex roles and permissions to be defined and enforced.

##### sessions
Provides session management that is scalable and really easy to use!

##### tinymce
Provides the TinyMCE editor

##### users
Provides user functionality.

### Writing our first controller
Before we can output anything to the screen we must create a controller.  First create the directory **DOCUMENT_ROOT/adapt/applications/my_app/controllers**

Create a new file named **controller_root.php** in this directory with the following:
```php
<?php

namespace applications\my_app{


}

?>
```
