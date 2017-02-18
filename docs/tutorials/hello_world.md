# Hello world application

Because everyone loves 'Hello world'.

If you haven't already done so, install Adapt and configure your database.

After installation has completed adapts bundle directory ***document root/adapt/*** will contain several bundles including adapt_setup which is the application that is currently configured to run.  Before we switch to our application, we must first build our application.

Lets create a new bundle folder called **hello_world** and inside this folder another labelled **hello-world-1.0.0**, inside this folder create **classes**, **controllers**, **models** and **views**.

We should be left with a folder structure like this:
```
DOCUMENT_ROOT/adapt/hello_world
DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0
DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0/classes
DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0/controllers
DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0/models
DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0/views
```

We need to tell Adapt about out bundle so go ahead and create **DOCUMENT_ROOT/adapt/hello_world/hello_world-1.0.0/bundle.xml**:
```xml

``
