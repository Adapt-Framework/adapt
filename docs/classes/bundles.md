# Class `bundles`
The heart of bundle management in adapt.  Generally you shoudn't need to manage bundles directly, if however the need arises, this is the class for the job.

## Table of contents
- [Methods](#methods)
    - [has](#hasbundle_name)
    - [get](#getbundle_name)
    - [cache](#cachekey-bundle)
    - [search_respository](#search_repositoryq)
    - [download](#downloadbundle_name)
    - [unbundle](#unbundlebundle_file_path)
    - [bundle](#bundlebundle_path)
    - [boot](#bootbundle_name--null)
    - [apply_global_settings](#apply_global_settings)
    - [load_settings](#load_settings)

## Methods
### has(`$bundle_name`)
Is a bundle avaialble locally?

#### Input
- `$bundle_name` The name of the bundle

#### Returns:
- `true` or `false`

--

### get(`$bundle_name`)
Returns the bundle `$bundle_name`, if the bundle is not available locally it is sourced, downloaded and installed.

--

#### Input:
- `$bundle_name` The name of the bundle you would like to return.

#### Returns:
- [`bundle`](/docs/classes/bundle.md) or `null` when a bundle is not available.  See [base::errors()](/docs/classes/base.md#errorsclear--true) to find out why a bundle couldn't load.

--

### cache(`$key`, `$bundle`)
Cache the bundle for late use.  This is used interally and should never need to be called from outside this class.

#### Input:
- `$key` A unique string used to store this bundle against.
- [`bundle`](/docs/classes/bundle) The bundle you would like to store.

--

### search_repository(`$q`)
Searches the repository for a bundle named `$q` and returns an `array` of results.

**IMPORTANT:** The repository hasn't yet been built and there is a very make shift version running to allow installs of Adapt.  This function will return one result indicating that the bundle was found, this is not the case.  This only happens to prevent the installer from failing.

#### Input:
- `$q` A search string

#### Returns:
- `array()` **IMPORTANT:** The format of this array hasn't yet been finalised.

--

### download(`$bundle_name`)
Downloads a bundle from the repository if it exists.

**IMPORTANT:** The repository hasn't yet been built, however, this function works as expected.

#### Input:
- `$bundle_name` The name of the bundle that you would like to download.

#### Returns:
- `true` or `false` When true you can use [`get(..)`](#getbundle_name) to get the bundle.

--

### unbundle(`$bundle_file_path`)
Unbundles a .bundle file into the correct location.

#### Input:
- `$bundle_fle_path` The full path of the bundle.

#### Returns:
- `true` or `false` indicating if the operation succeeded or not.

--

### bundle(`$bundle_path`)
Ummm, ok, so... it turns out this function is empty... thats an oversight on my part. I will fix this asap, in the meantime in the `cli_tools/` folder there is a command line tool called `bundle` that takes two arguements, the first is the **path** to the bundle.xml (do not include bundle.xml in the path) and the second is the filename to output, this should always be <bundle_name>.bundle

#### Input:
- `$bundle_path` The path to the bundle.

#### Returns:
- At present returns nothing, does nothing!

--

### boot(`$bundle_name = null`)
Boots a bundle and it's dependencies.  Calling this method without any params will cause the boot process to boot the default application.  The default application is detirmined by the bundle specified in the setting `adapt.default_application` or when not set the first bundle listed in the applications directory.

#### Input:
- `$bundle_name` (Optional)

#### Returns:
- `true` or `false` indicating success or failure.

--

### apply_global_settings()

--

### load_settings()
