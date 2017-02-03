# View controllers
At the very heart of MVC design, please welcome, the humble view controller.

## Introduction
In Adapt view controllers and URL rooting go hand in hand.  Any bundle can define view controllers but only application bundles can define root view controllers.  Root controllers are the same as any other controller but they are automatically mounted to the URL path **/**.

Lets look at the following root view controller.

**/adapt/test_app/test_app-1.0.0/controllers/controller_root.php**:
```php
<?php
namespace test_app;

defined('ADAPT_STARTED') or die;

class controller_root extends \adapt\controller{

  public function view_default(){
    
  }

}
```
Because this controller is named **controller_root** and the bundle it's a part of (test_app) is an application, then this controller is automatically mounted by Adapt.

Calling the URL **/** from a browser will invoke the method **view_default** on this controller.  Creating a new method called **view_foobar** would be mounted against the URL **/foobar**. 

**view_** methods can do one of three things:
1. Add content to the DOM by using the controllers **add_view** method.
2. Return a value, causing only that value to be output to the browser.  Useful for AJAX calls or building API's.
3. Return another controller and hand off the remaining portion of the URL to the other controller.

### Adding content to the DOM
We can add content to the DOM by using the controllers **add_view** method like so:
```php
public function view_default(){
  $this->add_view("Some simple text");
  $this->add_view(new html_h1("A heading"));
  $this->add_view(["An array", new html_strong("of things")]);
}
```

### Outputing without the DOM
If you only wish to output certain content without the entire DOM, you can simple return something from the method:
```php
public function view_default(){
  return "Only this text will be sent to the browser";
}
```

### Passing off to another controller
To pass control to another view controller you could simply return the controller from within the **view_** method, however, Adapt routes actions and views seperately, returning a controller directly will cause routing issues.

Lets say we want to mount the controller **controller_foo** to the URL **/bar**, on the **controller_root** we would create the following method:
```php
public function view_bar(){
  return $this->load_controller("\\test_app\\controller_foo");
}
```

This is the only offically supported way of returning controllers.

In the above example, the URL **/bar** would be serviced by **view_default** on **controller_foo**.  To service the URL **/bar/humbug** you simply need to add a method named **view_humbug** on **controller_foo**
