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
* Add content to the DOM by using the controllers **add_view** method.
* Return a value, causing only that value to be output to the browser.  Useful for AJAX calls or building API's.
* Return another controller and hand off the remaining portion of the URL to the other controller.

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

In the above example, the URL **/bar** would be serviced by **view_default** on **controller_foo**.  To service the URL **/bar/humbug** you simply need to add a method named **view_humbug** on **controller_foo**.

## Actions
Lets start by defining a new action named **some-action** on the route controller:
```php
<?php
namespace test_app;

defined('ADAPT_STARTED') or die;

class controller_root extends \adapt\controller{
  
  public function action_some_action(){
  
  }
  
  public function view_default(){
    
  }
}
```

In Adapt, actions and views are routed seperately. calling the URL **/some-action** will always invoke the method **view_some_action** on **controller_root**.  Also note the - in the URL is translated to _ in the method.

To invoke the method **action_some_action** we would call it either with the URL **/?actions=some-action** or by submitting the actions variable from within a form.

By seperating actions from views we are able to control the output view very easily, we can also perform multiple actions per call, we just simply comma seperate them, eg **/?actions=some-action,another-action**.

Even though actions and views are routed seperately, we still use **view_** methods to control routing to the action.  Lets say we have the root controller (**controller_root**) and another controller handling user accounts (**controller_user**) and we wanted to invoke **action_login** on that controller while displaying **view_foobar** on **controller_root**.

The URL would look like this:
```
/foobar?actions=users/login
```

Our **controller_root** would look like:
```php
<?php
namespace test_app;

defined('ADAPT_STARTED') or die;

class controller_root extends \adapt\controller{
  
  public function view_default(){
    
  }
  
  public function view_foobar(){
    $this->add_view("Welcome to foobar!");
  }
  
  public function view_users(){
    return $this->load_controller("\\test_app\\controller_users");
  }
}
```

Our action began **users/...**, notice that the **controller_root** used a **view_** method to route to the user controller.

**controller_user** would look something like this:
```php
<?php
namespace test_app;

defined('ADAPT_STARTED') or die;

class controller_users extends \adapt\controller{
  
  public function action_login(){
  
  }
  
  public function view_default(){
    
  }
}
```

Only the final part of an action URL invokes an **action_** method.

## Handling user input
Lets assume we want to handle a user login with a username and password, we can access the form data directly from within the action like so:

```php
<?php
namespace test_app;

defined('ADAPT_STARTED') or die;

class controller_root extends \adapt\controller{
  
  public function action_login(){
    $username = $this->request['username'];
    $password = $this->request['password'];
    
    // Do something the username and password
    if ($success){
      $this->redirect("/my-account");
    }else{
      $this->respond("login", "Login failed");
      $this->redirect("/login");
    }
  }
  
  public function view_default(){
    
  }
  
  public function view_login(){
    $this->add_view("TODO: Write login form");
  }
  
  public function view_my_account(){
    $this->add_view("My account page");
  }
}
```
In the above example we can send a response back from the action with
```php
$this->respond('key', 'value');
```
To access the response from within a **view_** method you can do the following:
```php
$response = $this->reponse('key');
```
To prevent duplicate form submissions it is recomended the after processing an action you call the redirect method and redirect to a view.  Any data set with the respond method is preserved after the redirect.  In addition, if we were processing multiple actions, the redirect would occur after the last action is processed.  If multiple actions call the redirect method, then the last to call wins.
