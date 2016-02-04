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

* `index.php` This is the first file called for each web request, it's job is to start Adapt.  You should never need to change anything in this file.
* `adapt/` Is a directory containing all the code and assets.
* `adapt/settings.xml` Contains global settings, if you created a database connection during install it will be saved here.
* `adapt/adapt/` This contains the Adapt framework bundle, all the code in here is framework code.
* `adapt/adapt_setup/` This contains a web application bundle called 'Adapt Setup'.  This application bundle was installed during installation and provided an interface to setup a database connection.
* `adapt/bootstrap/` Contains the popular Bootstrap CSS framework.  This bundle was installed by the adapt_setup bundle.  See [GetBootstrap.com](http://getbootstrap.com) for more.
* `adapt/bootstrap_model_manager/` A bundle that extends ```adapt/bootstrap/``` to allow multiple modals to exist at the sametime.  This bundle was installed by adapt_setp bundle.
* `adapt/bootstrap_views/` A bundle that provides easy to use views of all the Bootstrap componets.  The bundle was installed by the adapt_setup bundle.
* `adapt/font_awesome/` A bundle containing all the awesomeness that is Font Awesome. The bundle was installed by the adapt_setup bundle. See [FontAwesome.io](http://fontawesome.io) for more.
* `adapt/font_awesome_views/` A bundle containing easy to use views for use with Font Awesome.  This bundle was installed by adapt_setup bundle.
* `adapt/jquery/` A bundle containing jQuery. The bundle was installed by the adapt_setup bundle. See [jQuery.com](http://jquery.com) for more.
* `adapt/jquery_ui/` A bundle containing jQuery UI. The bundle was installed by the adapt_setup bundle. See [jQueryUI.com](http://jqueryui.com) for more.
* `adapt/store/` Is used to store data from any bundle.  See [File Storage System](/docs/articles/file_storage_system.md) for more.
* `adapt/first_web_application/` This directory holds everything required for our application. All the files you change are in here.

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

* `first_web_application-1.0.0/bundle.xml` Contains key information about the bundle, see the sub heading below labled bundle.xml. 
* `first_web_application-1.0.0/classes/` Contains classes for this bundle. 
* `first_web_application-1.0.0/classes/bundle_first_web_application.php` Contains the bundles boot process and performs any actions required during installation.  See [Working with bundles](/docs/articles/working_with_bundles.md) for more information.
* `first_web_application-1.0.0/controllers/` Contains the view controllers for this bundle.
* `first_web_application-1.0.0/controllers/controller_root.php` The main view controller for the application.  This controller is responsible for URL routing for your site.
* `first_web_application-1.0.0/docs/` The documentation for the bundle.
* `first_web_application-1.0.0/models/` Contains the models for this bundle.
* `first_web_application-1.0.0/static/` Contains any static content this bundle uses.
* `first_web_application-1.0.0/views/` Contains the views for this bundle.

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

The mandatory elements of the bundle are as follows:

Element         | Description
----------------|-----------------
**name**        | The name of the bundle. If this bundle is published in the Adapt repository then the name must be unique.
**version**     | The version of the bundle.  This must always be in the format X.X.X
**label**       | A label for the bundle.  Think of **name** as the internal name and the label as the public facing name.
**namespace**   | The namespace used by the bundle.
**type**        | This tells Adapt what type of bundle this is.  The bundle we are building is a type of **application**, this means this bundle is responsible for controlling the website.  There are other types of bundles that can be used to add functionality to other bundles.  We will look more at types later.
**description** | A nice human readable description of the bundle.


Additionally you can include the following optional elements:

Element         | Description
----------------|-----------------
**copyright**   | Who the copyright holder of this bundle is
**license**     | License information about this bundle.
**website**     | A link to the authors website
**depends_on**  | A list of bundles this bundle depends on.
**settings**    | A complex type allowing you to define new settings or over-ride existing settings.  See [Working with settings](/docs/articles/working_with_settings.md) for more information.
**schema**      | A complex type allowing you to define the database schema or change an existing schema.  See [Working with databases](/docs/articles/working_with_databases.md) for more information.


## Writing your first web app - Hello world.

Lets start with something really simple, go ahead and open the root view controller (`<DOCUMENT ROOT>/adapt/first_web_application/first_web_application-1.0.0/controllers/controller_root.php`).

The file will look something like this:

```php

namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
    }

}

```

The first thing you'll notice is that this class is in the namespace `first_web_application`, it's important that the namespace you use is the same as you declared in `bundle.xml`.

The second line `defined('ADAPT_STARTED') or die;` is required in all PHP files, this ensures that the file is not called directly from the browser.

If your website address is www.example.com/ then the root controller is mapped directly to the website address.

Go ahead and add a public method called **view_default()** giving you a file like:

```php

namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
        
        }
        
    }

}

```

`view_default()` will be called when ever anyone visits www.example.com/ so lets make it do something interesting.  Add the following to the method:


```php
$this->add_view(new html_h1("Hello World"));
```

Giving you a root controller that looks like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            
        }
        
    }

}
```

If you open up your brower and point it to your website address you'll see Hello World inside a H1 tag.

In the above example we used a class called `html_h1` to generate our html, you may notice that this class doesn't exist anywhere in any namespace, this is because Adapt uses special classes call handlers, you can read more about handlers in the aricle [Working with class handlers](/docs/articles/working_with_class_handlers.md).

Lets add some other simple content to the page to see class handlers in action, we will start by adding a paragraph with following code:

```php
$this->add_view(new html_p("This is a paragraph"));
```

And lets add something a little more complicated such as a list:

```php
$this->add_view(
    new html_ul(
        array(
            new html_li("Item 1"),
            new html_li("Item 2"),
            new html_li(array("Item ", new html_strong("3")))
        )
    )
);
```


Giving us a view controller that looks like:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
    }

}
```

### Routing URL's
The above example is great if you have only a single page, so lets extend our first_web_application to include addition URL mappings.

We are going to map www.example.com/about to our root controller, in Adapt this is as easy as creating a new method.

Lets add the method **view_about()** to our controller:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
        
        }
        
    }

}
```

Whenever anyone vists www.example.com/about this function will be called, lets add something simple to method to make it display something. Go ahead add the following to the method:

```php
$this->add_view(new html_h1("About"));
$this->add_view(new html_p("This is the about us page"));
```

Giving us a controller that looks like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
    }

}
```

Because Adapt maps URL's directly to functions you can only have URL's that are valid as a function name. For example if you wanted to map www.example.com/test! you would be unable to due to the fact that you can not call a method **view_test!()** in PHP.

Adapt treats hypens '-' the same as underscores in URL's so if you wanted a URL of www.example.com/this-is-a-page you could do this by creating a function called **view_this_is_a_page()**, this function would also be available via www.example.com/this_is_a_page.

If we want to use deeper URL's such as www.example.com/hello/world we need to create a new controller to handle the second level, so lets do that now.

Create a new controller with the following code and save it as `<DOCUMENT ROOT>/adapt/first_web_application/first_web_application-1.0.0/controllers/controller_hello.php`:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_hello extends \adapt\controller{
        
        public function view_default(){
            
        }
        
        
    }

}
```

The first thing we have to do is map the first part of our URL (**www.example.com/hello**/world) to the new controller, to do this we need to add a new function called **view_hello()** to our controller_root.  So go ahead and do that so your controller_root looks like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
        
        }
        
    }

}
```

Instead of adding content to the page we want this function to pass control over to another controller, to do this we need to add the following to our **view_hello()** function:

```php
return $this->load_controller("controller_hello");
```

Which will make our controller_root look like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
    }

}
```

We could have also used the following code:
```php
return new controller_hello();
```

However doing this will cause actions to fail, in Adapt the only offical way to load controllers is via the **load_controller()** method.

Now we have mapped our new controller we can access the **view_default()** on the new controller by visiting www.example.com/hello.

To access www.example.com/hello/world we need to create a new method called **view_world()** on the new controller named controller_hello, leaving it looking like:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_hello extends \adapt\controller{
        
        public function view_default(){
            
        }
        
        public function view_world(){
        
        }
    }

}
```

Let's go ahead and add the following to our **view_world** method:

```php
$this->add_view(new html_p("You are seeing this because you went to the URL /hello/world"));
```

So that controller_hello looks like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_hello extends \adapt\controller{
        
        public function view_default(){
            
        }
        
        public function view_world(){
            $this->add_view(new html_p("You are seeing this because you went to the URL /hello/world"));
        }
    }

}
```

This is a basic introduction to URL routing, for more advanced routing please see [URL Routing](/docs/articles/url_routing.md).

### Building content with view controllers

In the above examples we used controller to do some basic routing, we can also use controllers to build our page step by step. Lets say you want a common header and footer on each page, you can add this to the controller_root and every page on the site will then have the same header and footer.  This allows you to write everything only once.

Lets update our first_web_application to add a custom page header and footer to the site.  When we access www.example.com/ www.example/about www.example.com/hello or www.example.com/hello/world we will see the header and footer.

The first thing we need to do is create a new property on our controller_root so that we can store main page content, not the header and footer.

Add a protected property called `$_content` to your controller_root, like so:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
    }

}
```

The next step is to add a constructer to the controller so that we can set the `$_content` property to be an empty div element, like so:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
    }

}
```

Also noticed that we called `parent::__construct()` without doing this our controller will fail to load.

The next step is move our content so that it is added to our new `$_content` property instead of the main page, to do this we are going to over-ride the method `add_view()` so that it adds the content to `$_content`.

So lets do it:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
    }

}
```

At this stage if we were to view the site we would see nothing because all the content is stored in `$_content` but we haven't added `$_content` to the page yet, before we add the `$_content` to the page we need to first add our custom header.

We could add our header to **view_default** but this will mean it will only be visable when someone visits www.example.com/, to make it visable on all pages we need to add the content in our constructor.

For the purposes of this example, we are going to create a simple header that looks like this:
```html
<header>
    <h1>example.com</h1>
    <p>This is the header</p>
</header>
```

In Adapt we would write this:
```php
$header = new html_header(
    array(
        new html_h1("example.com"),
        new html_p("This is the header")
    )
);
```

This code will create a new variable called `$header` which contains our header, we need to add `$header` to the page to be useful.  In previous examples we used `$this->add_view(...)` to add content, unfortunatly we have overriden this and so using it will cause `$header` to be added to `$_content` which isn't what we want.  So to add `$header` to the page we need to use the parent's add_view method like so:

```php
parent::add_view($header);
```

This will leave the controller_root looking like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
    }

}
```

If we view the site now we will see the header on every page but not the content.

We need to add `$_content` to the page in our constructor using `parent::add_view(...)` like so:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
            
            parent::add_view($this->_content);
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
    }

}
```

Now when we visit a page we will see the header on every page and the content for the page we are viewing.

Lets add a really simple footer, such as:
```html
<footer>
    <p>Copyright 2016</p>
</footer>
```

In Adapt this is written as:
```php
$footer = new html_footer(new html_p("Copyright 2016"));
```

And to add it to the page:
```php
parent::add_view($footer);
```

Making our controller_root look like:
```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
            
            parent::add_view($this->_content);
            
            $footer = new html_footer(new html_p("Copyright 2016"));
            parent::add_view($footer);
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
    }

}
```

Viewing the site now will show a header and footer on every page with the correct content for the page.  We can add additional controllers to our site and all of them will automatically get the header and footer.

## Working with views
So far we have looked at how to use view controllers to map URL's and build pages, in this section we will look at views and how to use them.

To start with lets look a little more at the `html` class hander.

We saw in previous examples how to create simple html elements, lets look at this in more detail.

The first thing to note is the `html` class is printable.

This:
```php
print new html_p("My text");
```

Becomes:
```html
<p>My text</p>
```

We can also specify parameters when constructing.  To add a class `red` to the p tag we can do one of the following:
```php
print new html_p("My Text", array('class' => 'red));
```
**OR**
```php
$p = new html_p("My Text");
$p->attr('class', 'red');
print $p;
```
**OR**
```php
$p = new html_p(array('class' => 'red'));
$p->add("My Text");
print $p;
```
**OR**
```php
$p = new html_p();
$p->add("My Text");
$p->attr('class', 'red');
print $p;
```

All of the above examples will output:
```html
<p class="red">My Text</p>
```

### Building our first view
So far we have used the `html` handler class to generate some basic html, lets take this future and create a view.

Create a new file called `view_login_form.php` and save it in `<DOCUMENT ROOT>/adapt/first_web_application/first_web_application-1.0.0/views/view_login_form.php`.

Write the following in the new file:

```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
    
    }
}
```

We are going to use this view to create a simple user login form.

The first thing we need to do is add a constructor, views may have custom constructors as long as they call the parents constructor, the first parameter the parent requires is the type of tag this view is, this always defaults to div.

So lets add the constructor:
```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
        
        public function __construct(){
            parent::__construct('div');
        }
        
    }
}
```

At this point if we were to print this view like this:
```php
print new view_login_form();
```

Would output:
```html
<div class="view login-form"></div>
```

Lets add a form element to our view, first by declaring it and then next by adding it with the following code:
```php
$form = new html_form(array('action' => '/', method => 'post'));
$this->add($form);
```

So that our view looks like:
```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
        
        public function __construct(){
            parent::__construct('div');
            
            $form = new html_form(array('action' => '/', method => 'post'));
            $this->add($form);
        }
        
    }
}
```


if we print out our view now we will get:
```html
<div class="view login-form">
    <form action="/" method="post"></form>
</div>
```

Lets go ahead and some fields and labels to the form, like so:

```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
        
        public function __construct(){
            parent::__construct('div');
            
            $form = new html_form(array('action' => '/', method => 'post'));
            $this->add($form);
            
            //Add the username
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-username')),
                        new html_input(array('name' => 'username', 'id' => 'id-username', 'type' => 'text'))
                    )
                )
            );
            
            //Add the password
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-password')),
                        new html_input(array('name' => 'username', 'id' => 'id-password', 'type' => 'password'))
                    )
                )
            );
        }
        
    }
}
```

Printing out our view will display the following:
```html
<div class="view login-form">
    <form action="/" method="post">
        <div>
            <label for="id-username">Username</label>
            <input name="username" id="id-username" type="text">
        </div>
        <div>
            <label for="id-username">Password</label>
            <input name="password" id="id-password" type="password">
        </div>
    </form>
</div>
```

We should also add a submit button like so:
```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
        
        public function __construct(){
            parent::__construct('div');
            
            $form = new html_form(array('action' => '/', method => 'post'));
            $this->add($form);
            
            //Add the username
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-username')),
                        new html_input(array('name' => 'username', 'id' => 'id-username', 'type' => 'text'))
                    )
                )
            );
            
            //Add the password
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-password')),
                        new html_input(array('name' => 'username', 'id' => 'id-password', 'type' => 'password'))
                    )
                )
            );
            
            //Add a submit button
            $form->add(new html_submit());
        }
        
    }
}
```

Now that we have created our view we can call it from our view controller, so lets map www.example.com/login to our view.

Open up the controller_root and add the method **view_login**, so it looks like:
```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
            
            parent::add_view($this->_content);
            
            $footer = new html_footer(new html_p("Copyright 2016"));
            parent::add_view($footer);
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
        
        public function view_login(){
            
        }
    }

}
```

We can then add our view to the page with the following:
```php
$this->add_view(new view_login_form());
```

Leaving our view controller looking like this:
```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
            
            parent::add_view($this->_content);
            
            $footer = new html_footer(new html_p("Copyright 2016"));
            parent::add_view($footer);
        }
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
        
        public function view_login(){
            $this->add_view(new view_login_form());
        }
    }

}
```

Going to www.example.com/login will display the header we created earlier, the login view we just created followed by the footer.

## Handling user input
Up until now we have just served content, what if we want to do something?

Earlier we created a view with a login form, so lets look at how we would process that form.

When we created our view controller we learned that we could map views directly to URLs by prefixing the method with **view_** which is great for displaying view but isn't very useful for processing user input.  To do this view controllers offer a second prefix **action_** which allows you to route actions, in addition Adapt allows actions to be chained together, more on that later.

So for us to process our form we need to first create an action to handle the request.

So lets open up our controller_root and add a new method called **action_login()**, you can add this anywhere to the class, however, we'd prefer that you add it above any views so anyone else reading your code and easily find the actions.

Your controller_root should now look like this:

```php
namespace first_web_application{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends \adapt\controller{
        
        protected $_content;
        
        public function __construct(){
            parent::__construct();
            $this->_content = new html_div();
            
            $header = new html_header(
                array(
                    new html_h1("example.com"),
                    new html_p("This is the header")
                )
            );
            
            parent::add_view($header);
            
            parent::add_view($this->_content);
            
            $footer = new html_footer(new html_p("Copyright 2016"));
            parent::add_view($footer);
        }
        
        
        public function action_login(){
            
        }
        
        
        public function view_default(){
            
            $this->add_view(new html_h1("Hello World"));
            $this->add_view(new html_p("This is a paragraph"));
            
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Item 1"),
                        new html_li("Item 2"),
                        new html_li(array("Item ", new html_strong("3")))
                    )
                )
            );
            
        }
        
        
        public function view_about(){
            $this->add_view(new html_h1("About"));
            $this->add_view(new html_p("This is the about us page"));
        }
        
        public function view_hello(){
            return $this->load_controller("controller_hello");
        }
        
        public function add_view($content){
            /* This function is overriding parent::add_view */
            $this->_content->add($content);
        }
        
        public function view_login(){
            $this->add_view(new view_login_form());
        }
    }

}
```

Unlike views, actions cannot be called directly from the URL.  Browsing to www.example.com/login will call **view_login** and not **action_login**, to call actions we must must submit something to the server.

In order for Adapt to know which action to call we must tie it to our form.  Lets open up our **view_login_form**, we need to add a new hidden input with a name called 'actions' and a value of 'login'.  After you've done this your view will look like this:

```php
namespace first_web_application{

    defined('ADAPT_STARTED') or die;
    
    class view_login_form extends \adapt\view{
        
        public function __construct(){
            parent::__construct('div');
            
            $form = new html_form(array('action' => '/', method => 'post'));
            $this->add($form);
            
            //Add the action
            $form->add(new html_input(array('type' => 'hidden', 'name' => 'actions', 'value' => 'login')));
            
            //Add the username
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-username')),
                        new html_input(array('name' => 'username', 'id' => 'id-username', 'type' => 'text'))
                    )
                )
            );
            
            //Add the password
            $form->add(
                new html_div(
                    array(
                        new html_label("Username", array('for' => 'id-password')),
                        new html_input(array('name' => 'username', 'id' => 'id-password', 'type' => 'password'))
                    )
                )
            );
            
            //Add a submit button
            $form->add(new html_submit());
        }
        
    }
}
```

The form now contains the information for Adapt to process it, upon submitting the form, Adapt will trigger the method **action_login()** followed by **view_default()** on `controller_root`.  The reason the view is also triggered is because the forms action attribute is set to `/`, you could change it to `/hello/world` which would cause it to call **action_login()** on `controller_root` followed by **view_world** on `controller_hello`.

You can chain actions together by comma seperating them in the hidden action input on the form.  For example, to call **action_login** on `controller_root` followed by **action_something_else** on `controller_hello` (This action doesn't exist, just pretend it does) you would set the hidden action inputs value to `login,hello/something_else`.  Notice how we had to provide the path to the second controller, also notice that unlike views, action paths do not start with a `/`.

**IMPORTANT NOTE:** When routing views you can use relative paths by excluding the leading `/` in the same way you can in HTML. When routing actions the path is **always** absolute and it should never start with a leading `/`.  (Side note, you probably think this is silly and you're probably right.  If I'm honest until now I'd never thought about it.  This will probably change in the future).

