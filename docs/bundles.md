# Bundles

## What are bundles?
In Adapt everything is a bundle, Adapt is even a bundle.  A bundle is just a bunch of useful code that does something.  A bundle could be a framework like Adapt, it could be locality information, or a form libaray or even a fully blown web application.  The idea is simple, you break code in to small independent useful chucks of code, bundle it up with a name and a version and then include it in future work.

Bundles can depend on other bundles, so instead of writing large applications, you write a smaller simpler bundle that pulls in functionality from other bundles.  Take for example the **Administrator** bundle, this provides a rich website administration tool, featuring platform and user management, including this means you only need to write that which is custom to your application. **Administrator** of course has lots of dependiences, which in turn have more.

By building this way we can solve problems truly once.

To include **Administrator** in your application you need only reference it, Adapt will download and install it the next time your application runs.

Outside of the framework is the Adapt repository, a centralised place where bundles are stored.  The repository is open to anyone and anyone can publish bundles, of course you have the option of making them public or private.

By integrating the repository into the fabric of the framework we can automatically install, update, upgrade and self heal when files go missing.  When you write applications with Adapt then you also get the ability to deploy on demand.

Because bundles are versioned and Adapt has been designed to handle updates it becomes possible to provide continuous deployment for any application you write.

## Bundle file structure
Bundles have a typical file structure as indicated below, this however is not set in stone and bundles may include whatever they wish.
```
/
/bundle.xml
/controllers    <Directory>
/classes        <Directory>
/models         <Directory>
/views          <Directory>
/static         <Directory>
```

The directories for models, views and controllers hold as expected, models, views and controllers. The classes directory is for any classes that are not models, views or controllers.  The static directory holds any static content your bundle needs.

## bundle.xml
This file tells Adapt about your bundle.  This file contains meta data such as the bundles name and version, a label and description for display in the repository should you publish it.  This file is also used to define the data schema used by your bundle and any dependencies your bundle has.

A typical bundle.xml will have the following structure:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<adapt_framework>
    <bundle>
        <label>My First Web App</label>
        <name>my_app</name>
        <version>1.0.0</version>
        <type>application</type>
        <namespace>\my_namespace</namespace>
        <description>Test application</description>
    </bundle>
</adapt_framework>
```

### Available bundle elements
Name            | Required      | Description
----------------|---------------|----------------
name            | Always        | The name of the bundle. This should be all lowercase alpha chars or underscores. If publishing to Adapt repository then the name must also be unique.
version         | Always        | This is the version of the bundle, this should be in the form of xx.xx.xx following the rules of [http://semver.org/](http://semver.org/). When publishing to the repository you may re-publish at anytime but the version must be different from all previous published versions.
label           | Always        | A human friendly label describing the bundle.  When publishing to the repository the label from the most recent version will be used to the listing.
description    | Only when posting to the public repository | A rich description of the bundle using only plan text.
type           | Always         | This tells Adapt what your bundle is used for, you can obtain a list of valid types from [https://repository.adaptframework.com/v1/bundle-types](https://repository.adaptframework.com/v1/bundle-types), when building an application it must always be **application**
namespace       | Always        | The namespace being used by the bundle, for example, **\adapt**.  The namespace here must be used for all models, views and controllers.  You should also use it for all other classes.  You can include more namespaces when your bundle is booting, see bundle control below.
version_status | When posting to Adapt repository | Can be one of **alpha**, **beta**, **release_candidate**, **release**. By allowing development versions in the repository we can offer continious build testing to dev teams.
availability   | When posting to Adapt repository | Can be one of **public** of **private**.  **Important note:** Bundles pushed to the repository with a status of **public** may not be withdrawn at a later date.  Because the nature of Adapt is building small blocks, pulling a small one used in many web applications would break alot of things.  If you tell us its **public**, it's public


### authors
To provide information about the authors of a bundle you can use:
```xml
<authors>
    <author>
        <name>Matt Bruton</name>
        <email>matt.bruton@gmail.com</email>
    </author>
</authors>
```

### Contributers
```xml
<contributors>
    <contributor>
        <name>Joe Hockaday</name>
        <email>jdhockad@hotmail.com</email>
    </contributor>
    <contributor>
        <name>Sion Purnell</name>
        <email>yepitsmesion@hotmail.co.uk</email>
    </contributor>
</contributors>
```

### Vendor
To provide information about the bundles vendors use:
```xml
<vendor>
    <name>Adapt Framework</name>
    <website>https://www.adaptframework.com</website>
</vendor>
```

### depends_on
Use this tag to tell adapt about any dependencies you require.  You must always provide the **name** element, the **version** element is optional.

When specifing a version it should always be in the format **X** or **X.X**, you shoudn't provide a revision.

For example, to include jQuery and Adapt Administrator:
```xml
<depends_on>
    <bundle>
        <name>jquery</name>
    </bundle>
    <bundle>
        <name>administrator</name>
        <version>1</version>
    </bundle>
</depends_on>
```

### settings
Your bundle may wish to include settings, Adapt supports a key/value system.

You can define settings like so:
```xml
<settings>
    <category name="XML &amp; HTML Settings">
      <setting>
        <name>xml.readable</name>
        <label>Output readable XML/HTML?</label>
        <default_value>No</default_value>
        <allowed_values>
          <value>Yes</value>
          <value>No</value>
        </allowed_values>
      </setting>
      <setting>
        <name>html.format</name>
        <label>HTML Format</label>
        <default_value>html5</default_value>
        <allowed_values>
          <value>html5</value>
          <value>xhtml</value>
        </allowed_values>
      </setting>
      <setting>
        <name>html.closed_tags</name>
        <label>Closed HTML tags</label>
        <default_values>
          <value>img</value>
          <value>link</value>
          <value>meta</value>
          <value>br</value>
          <value>hr</value>
          <value>area</value>
          <value>base</value>
          <value>col</value>
          <value>command</value>
          <value>embed</value>
          <value>input</value>
          <value>link</value>
          <value>meta</value>
          <value>param</value>
          <value>source</value>
        </default_values>
      </setting>
    </category>
</settings>
```

**allowed_values** is optional.  Note that **default_value** is used to default a single value, where as **default_values** allows an array to be specified.

The values of settings can be accessed at runtime from within any class like so:
```php
$value = $this->setting('some.setting.name');
```

You can also set the value at runtime, please note that this only remains for the duration of the current request. To override the setting completely enter the new value in **settings.xml** file.
```php
// Only set for the remainder of the current request
$this->setting('some.setting.name', 'new value');
```

### schema
This tag is used to define the data schema used by your bundle.  You can use it to add, change or remove database tables or add, change and remove database records.

Because Adapt bundles are versioned, you must ensure you use the **schema > remove** tag to remove tables no longer needed.

#### Define a table
```xml
<schema>
    <add>
        <table name="car">
            <field name="car_id" data-type="bigint" key="primary" auto-increment="Yes" label="Car #" description="This field holds the cars unique ID" />
            <field name="name" data-type="varchar" max-length="64" label="Name" description="Internal name" />
            <field name="label" data-type="varchar" max-length="128" label="Label" description="Display label" />
        </table>
    </add>
</schema>
```

Adapt supports a number of data types, more can be added via other bundles.  The **advanced_data_types** bundle provides things such as email address and IP addresses.  You can get a list of installed data types by looking in the **data_type** table that Adapt installs.

Name        | Notes                     | Name      | Notes
------------|---------------------------|-----------|---------------------------
tinyint     |                           | tinyblob  |
smallint    |                           | blob      |
mediumint   |                           | mediumblob |
int         |                           | longblob  |
integer     |                           | tinytext  |
bigint      |                           | text      |
serial      | Same as ```<field data-type="bigint" key="Primary" auto-increment="Yes" ... />``` | mediumtext | 
bit         |                           | longtext  | 
boolean     |                           | enum      | Eg: ```<field data-type="enum('Value 1', 'value 2')" default-value="Value" />```
bool        |                           | set       | 
float       |                           | year      | 
double      |                           | date      |
char        | ```<field data-type="char" max-length="32" />```  | time |
binary      |                           | datetime  | 
varchar     | ```<field data-type="varchar" max-length="64" />``` | timestamp | 
varbinary   |                           | guid      | 

#### Add records to a table
```xml
<schema>
    <add>
        <table name="car">
            <field name="car_id" data-type="bigint" key="primary" auto-increment="Yes" label="Car #" description="This field holds the cars unique ID" />
            <field name="name" data-type="varchar" max-length="64" label="Name" description="Internal name" />
            <field name="label" data-type="varchar" max-length="128" label="Label" description="Display label" />
            <record>
                <name>ka</name>
                <label>Ford Ka</label>
            </record>
            <record>
                <name>corsa</name>
                <label>Vaxhall Corsa</label>
            </record>
            <record>
                <name>ago</name>
                <label>Toyota Ago</label>
            </record>
        </table>
    </add>
</schema>
```

#### Referencing other field
In the next example we have a table for car, colour and car_colour.  At the time of installation we are unable to insert anything into car_colour unless we know the ID, Adapt solves this by allowing lookups.
```xml
<schema>
    <add>
        <table name="car">
            <field name="car_id" data-type="bigint" key="primary" auto-increment="Yes" label="Car #" description="This field holds the cars unique ID" />
            <field name="name" data-type="varchar" max-length="64" label="Name" description="Internal name" />
            <field name="label" data-type="varchar" max-length="128" label="Label" description="Display label" />
            <record>
                <name>ka</name>
                <label>Ford Ka</label>
            </record>
            <record>
                <name>corsa</name>
                <label>Vaxhall Corsa</label>
            </record>
            <record>
                <name>ago</name>
                <label>Toyota Ago</label>
            </record>
        </table>
        <table name="colour">
            <field name="colour_id" data-type="bigint" key="primary" auto-increment="Yes" label="Colour #" description="This field holds the colours unique ID" />
            <field name="name" data-type="varchar" max-length="64" label="Name" description="Internal name" />
            <field name="label" data-type="varchar" max-length="128" label="Label" description="Display label" />
            <record>
                <name>red</name>
                <label>Red</label>
            </record>
            <record>
                <name>blue</name>
                <label>Blue</label>
            </record>
        <table>
        <table name="car_colour">
            <field name="car_colour_id" data-type="bigint" key="Primary" auto-increment="Yes" />
            <field name="car_id" data-type="bigint" key="Foreign" referenced-table-name="car" referenced-field-name="car_id" />
            <field name="colour_id" data-type="bigint" key="Foreign" referenced-table-name="colour" referenced-field-name="colour_id" />
            <record>
                <car_id get-from="car" where-name-is="ka" />
                <colour_id get-from="colour" where-name-is="blue" />
            </record>
        </table>
    </add>
</schema>
```

#### Modifying existing tables
You can append new fields to existing tables in the same way you define a new table, just list the fields you wish to add.

You can't modify existing fields unless your bundle defined the field in the first place.  Be sure to include a dependency with the **depends_on** tag when modifying tables from other bundles.

#### Removing tables or fields
To remove a table you simply remove the fields like so:
```xml
<schema>
    <remove>
        <table name="car">
            <field name="car_id" />
            <field name="name" />
            <field name="label" />
        </table>
    </remove>
</schema>
```
You can only remove fields that were defined by your bundle.  When all the fields are removed the table is removed.

Please be sure to list old no-longer needed fields in the remove section, so that your bundle doesn't leave old tables in place between version changes.

#### Removing records
```xml
<schema>
    <remove>
        <table name="car">
            <record>
                <name>corsa</name>
            </record>
        </table>
    </remove>
</schema>
```

### Custom tag handling
It's possible to define new tags for use by your bundle.  See the next section for more information.


## Bundle control
Each bundle can create a special bundle control class for running code at specific points, such as when bundle boots or is installed.

The class must be saved in the ```classes/``` directory and named ```bundle_<bundle_name>```.  Lets say your bundle is named 'cars' we would create the file ```classes/bundle_car.php```:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

}
```

### Bundle booting
Lets say we wanted to add a css file to the dom on boot:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function boot(){
        if (parent::boot()){
            
            // Add to the dom
            $this->dom->head->add(new html_link(['rel' => "stylesheet", 'type' => 'text/css', 'href' => "/adapt/cars/cars-{$this->version}/static/css/cars.css"]));

            return true;
        }

        return false;
    }

}
```

### Bundling installation
We can also do things during install:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function install(){
        if (parent::install()){
            
            // Do something useful

            return true;
        }

        return false;
    }

}
```

### Bundle upgrading
Or when updating or upgrading:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function update(){
        if (parent::update()){
            
            // Do something useful

            return true;
        }

        return false;
    }

    public function upgrade($version = null){
        if (parent::upgrade($version)){

            // The new version is provided

            return true;
        }

        return false;
    }

}
```

#### Custom bundle.xml tags
The **forms** extends the bundle.xml file format and allows other bundles to use bundle.xml to define forms.

To do this is a two step process, the first step is to read the tag data and store it somewhere useful, the second to process the data.

The reason for the two stage is approach is simply because Adapt reads the files out of order and so anything we depend on when processing the data may not yet be available.

Lets create a bundle control class for a bundle named 'cars', we need to tell Adapt when our class constructs that we are interested in being notified about a tag named cars:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function __construct($data){
        parent::__construct('cars', $data);

        $this->register_config_handler('cars', 'cars', 'process_cars_tag');
    }
}
```
In the above example, we called:
```php 
$this->register_config_hander('bundle_name', 'tag_name', 'method_to_handle');
```
This tells Adapt to pass on the content of any tags named **cars** in any bundle.xml file to the method 'process_cars_tag'.

Of course in our example we haven't yet created this method, so lets do so:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function __construct($data){
        parent::__construct('cars', $data);

        $this->register_config_handler('cars', 'cars', 'process_cars_tag');
    }

    public function process_cars_tag($bundle, $tag_data){

    }
}
```

Our **process_cars_tag** method receives two parameters, the first is an instance of ```\adapt\bundle``` and will be the bundle that defined the ```cars``` tag in its xml file.

This method will be called for each method that defines a **cars** tag.  You can get the bundle name and version like so:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{

    public function __construct($data){
        parent::__construct('cars', $data);

        $this->register_config_handler('cars', 'cars', 'process_cars_tag');
    }

    public function process_cars_tag($bundle, $tag_data){
        $bundle_name = $bundle->name;
        $bundle_version = $bundle->version;
    }
}
```

The second parameter ```$tag_data``` will be an instance of ```\adapt\xml``` and contains the data from the bundle.xml file for the **cars** tag.

Lets say we use our cars tag to add new cars to the database and we use the following data structure:
```xml
<adapt_framework>
    <bundle>
        <name>cars</name>
        <namespace>cars</namespace>
        <label>Cars app</label>
        <type>application</type>
        <version>1.0.0</version>
        <cars>
            <car name="capri" colour="blue">Ford Capri</car>
            <car name="escort" colour="red">Ford Escort</car>
        </cars
    </bundle>
</adapt_framework>
```

We need to process the data in ```$tag_data``` and store it somewhere useful.  Lets create a new property called ```$_processed_tag_data``` and process the tag:
```php
namespace cars;

class bundle_cars extends \adapt\bundle{
    
    protected $_processed_tag_data = [];

    public function __construct($data){
        parent::__construct('cars', $data);

        $this->register_config_handler('cars', 'cars', 'process_cars_tag');
    }

    public function process_cars_tag($bundle, $tag_data){
        $bundle_name = $bundle->name;
        $bundle_version = $bundle->version;

        // Check that $tag_data is well formed
        if ($tag_data instance of \adapt\xml && $tag_data->tag == "cars"){

            // Get the child notes
            $child_nodes = $tag_data->get();

           // Loop through them
           foreach($child_nodes as $node){
           
                // Make sure our $node is 'car' node
                if ($node instanceof \adapt\xml && $node->tag == "car"){
                    
                    // Store a record the record in our $_processed_tag_property
                    $this->_processed_tag_data[] = [
                        'label' => $node->text,
                        'name' => $node->attr('name'),
                        'colour' => $node->attr('colour')
                    ];
                }
           }

        }
    }
}
```

