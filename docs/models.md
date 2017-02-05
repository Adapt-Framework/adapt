# Models
Models made easy.  In Adapt models are always back by a database record, or collection of records. Because of this, defining a table is also to define a model.

To define a model we simply create a table using the **bundle.xml** file.

```xml
<?xml version="1.0" encoding="utf-8"?>
<adapt_framework>
  <bundle>
    <label>Test Application</label>
    <name>test_app</name>
    <version>1.0.0</version>
    <version_status>release</version_status>
    <type>application</type>
    <namespace>\test_app</namespace>
    <depends_on>
      <bundle>
        <name>adapt</name>
        <version>2.0</version>
      </bundle>
    </depends_on>
    <schema>
      <add>
        <table name="car">
          <field name="car_id" key="primary" auto-increment="Yes" data-type="bigint" />
          <field name="name" data-type="varchar" max-length="64" />
          <field name="label" data-type="varchar" max-length="64" />
          <field name="description" data-type="text" />
        </table>
      </add>
    </schema>
  </bundle>
</adapt_framework>
```
In the above example we've created a table called **car** with the field **car_id**, **name**, **label** and **description**.

To use this model to create a new **car** we simply do this:
```php
$car = new model_car();
$car->name = "capri";
$car->label = "Ford Capri";
$car->description = "Some description";
if ($car->save()){
  print $car->car_id;
}
```
The class **model_car** isn't explicity defined anywhere, yet still it will work as expected.  Whats more, models are namespaceless, it doesn't matter which namespace you're in, you can still just use the model as if it was part of the namespace your in.

There maybe times when you need models to act differently to the default behaviour, when this is the case you can define the model explicity in your bundle.  Lets concreate **model_car**:

**models/model_car.php**
```php
<?php

namespace test_app;

defined('ADAPT_STARTED') or die;

class model_car extends \adapt\model{
  
  public function __construct($id = null, $data_source = null){
    parent::__construct('car', $id, $data_source);
  }
  
  public function foo(){
    return "bar";
  }
  
}
```

In the above example we concreated **model_car** in the namespace **test_app**, this class can still be called within any namespace and it will work just fine.

```php
namespace another_namesapce;

$car = new model_car();
print $car->foo(); //Prints 'bar'
```

Adapt achieves this by aliasing models using inheritance, because of this if you need to test if two models are of the same type you should do the following:
```php
namespace another_namespace;

$car1 = new \test_app\model_car();
$car2 = new model_car();

if ($car1->table_name == $car2->table_name){
  print "They are the same type";
}
```
## Loading
There are four default ways to load models, you can of course concreate a model and define your own, or extend a concreated model and appended it, or extend Adapts base model and append your loader to all models.  

But lets look and the default loaders first.

### Loading by ID
If you know the record ID you can:
```php
$id = 1;
$car = new model_car($id);
```
Or if you care about success:
```php
$id = 1;
$car = new model_car();
if ($car->load($id)){
  // Do something
}else{
  // Or not
  print_r($car->errors(true));
}
```

### Loading by name
Some tables have a **name** field, think of this as internal name.  If a name is unique within a table you can load the record by the name.  Please be aware, Adapt does not force this field to be unqiue.
```php
$name = "capri";
$car = new model_car();

if ($car->load_by_name($name)){
  // Do something
}else{
  // Fail :(
  $errors = $car->errors(true);
}
```

### Loading by GUID
Lets say you want to use GUID's, you can do this by creating a field called 'guid' and giving it the data type of 'guid', like so:
```xml
<?xml version="1.0" encoding="utf-8"?>
<adapt_framework>
  <bundle>
    <label>Test Application</label>
    <name>test_app</name>
    <version>1.0.0</version>
    <version_status>release</version_status>
    <type>application</type>
    <namespace>\test_app</namespace>
    <depends_on>
      <bundle>
        <name>adapt</name>
        <version>2.0</version>
      </bundle>
    </depends_on>
    <schema>
      <add>
        <table name="car">
          <field name="car_id" key="primary" auto-increment="Yes" data-type="bigint" />
          <field name="guid" data-type="guid" />
          <field name="name" data-type="varchar" max-length="64" />
          <field name="label" data-type="varchar" max-length="64" />
          <field name="description" data-type="text" />
        </table>
      </add>
    </schema>
  </bundle>
</adapt_framework>
```
The GUID will be set automatically when the model is saved for the first time.  To load from a guid we simply:
```php
$guid = "xxxxxx-xxxxxxx-xxxx-xxxxxxxxxx";
$car = new model_car();
if ($car->load_by_guid($guid)){

}else{

}
```

### Loading by data
Sometime you may have a database record, to save time in reloading the data from the database, you can tell the model to use the raw data.
```php
<?php

class controller_root extends \adapt\controller{
  
  public function view_car(){
    $sql = $this->data_source->sql;
    $results = $sql->select('*')->from('car')->execute()->results();
    
    foreach($results as $result){
      $car = new model_car();
      if ($car->load_by_data($result)){
        $this->add_view(new html_pre(print_r($car->to_hash(), true)));
      }
    }
  }
  
}

```

### Checking if loaded
You can check if a model is loaded using the read-only property **is_loaded**.

```php
$car = new model_car(1); // Load record with car_id '1'
if ($car->is_loaded){
  print "Loaded '{$car->label}'\n";
}else{
  print "Unable to load because:\n";
  print_r($car->errors(true));
}
```

## Saving models
You can save a model using it's **save** method. Save will return true or false indicating success or failure.

```php
$car = new model_car();
$car->name = "escort";
if ($car->save()){
  print "Saved";
}else{
  print "Failed to save";
}
```

### Check if a model's changed
If you need find out if a model has changed, you can use the read-only property **has_changed**.
```php
$car = new model_car();
if ($car->has_changed){

}
```

## Exporting and importing data
Model provides some convienient methods for exporting and imported data.

### Exporting data
#### Hash array
You can export the data from a model using the **to_hash** method.
```php
$car = new model_car();
if ($car->load_by_name('capri')){
  $data = $car->to_hash();
  print $data['car']['label'];
}
```

The exported array will follow the structure:
```php
$data['table_name']['field_name'] = 'value';
```

#### Hash string array
This method provides a easy way to export data to a form and import data from a form.  In the structure of the data is the same as **to_hash** but it is flattened so the keys and values can be used as form input name and values.

```php
$car = new model_car();
if ($car->load_by_name('capri')){
  $data = $car->to_hash_string();
  print $data["car[label]"];
}
```

### Importing data
Data exported using **to_hash** or **to_hash_string** can be directly imported into the model using the **push** method.

```php
$car = new model_car(1);
$data = $car->to_hash();
$data['car']['description'] = "This car is fast";

$car->push($data);
$car->save();
```

