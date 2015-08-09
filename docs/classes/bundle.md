# Class `bundle`

**Inherits from:** [base](/docs/classes/base)

`bundle` is used by Adapt to manage individual bundles.  You shouldn't need to use this class as bundles are managed automatically.

## Table of contents
- [Constructing](#constructing)
- [Properies](#properties)
    - [is_loaded](#is_loaded)
    - [name](#name)
    - [booted](#booted)
    - [depends_on](#depends_on)
    - [bundle_path](#bundle_path)
- [Methods](#methods)
    - [load](#loadbundle_name)
    - [apply_settings](#apply_settings)
    - [boot](#boot)
    - [install](#install)
    - [save](#save)
    

## Constructing
### __construct(`$bundle_name = null`)
Creates an new bundle class and optionally loads a bundle, however this should never be used.  Instead please load bundles via [bundles](/docs/classes/bundles) as this is far more effiecent and quicker.
```php
/* You shouldn't do this */
$bundle = new bundle('adapt');

/* Or this */
$bundle = new bundle();
$bundle->load('adapt');

/* This is what you should do */
$bundles = new bundles();
$bundle = $bundles->get('adapt');
```

#### Input:
- `$bundle_name` The name of the bundle you would like to load.


## Properties
### is_loaded (RO)
Boolean indicating if the bundle been loaded.

--

### name (RO)
The name of the current loaded bundle.

--

### booted (RO)
A boolean indicating that the loaded bundle has been booted or not.

--

### depends_on (RO)
An array of bundle names that this bundle is dependent upon.

--

### bundle_path (RO)
The full path to the bundle.

--

## Methods
### load(`$bundle_name`)
Loads the bundle named `$bundle_name`.

#### Input:
- `$bundle_name` The name of the bundle you would like to load.

#### Returns:
- `true` or `false` indicating if the load succeeded or not.

--

### apply_settings()
Used by adapt during the boot process to apply global user settings to this bundle, effictivly overriding the defaults.

### boot()
Initialises the boot process for the bundle if it hasn't already been booted. This is managed automatically by adapt an you shoudn't need to boot a bundle manually.

#### Returns:
- `true` or `false` indicating if the bundle was booted successfully or not.  If a bundle has already been booted this will always return `true`.

--

### install()
Installs a bundle if it hasn't already been installed.  Note that this fuction assumes the bundle is locally available, this function will not pull bundles from the repository.  To install bundles you should include them in the depends_on section of your bundle.xml.

Alternativly you can install them using [bundles](/docs/classes/bundles) which will also download them if required.

--

### save()
Saves a bundle.  Really all that gets saved is **bundle.xml**, often the installer will add to **bundle.xml** and save it afterwards.
