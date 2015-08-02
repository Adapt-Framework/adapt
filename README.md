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


