<?php
/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
 *   
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *   
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *   
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     adapt
 * @author      Matt Bruton <matt.bruton@gmail.com>
 * @copyright   2016 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 *
 */

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Bundle management system used to download and install bundles.
     *
     * @property-read repository $repository
     * Returns a repository object used for talking to the Adapt repository.
     */ 
    class bundles extends base{
        
        /** @ignore */
        protected $_bundle_cache;
        
        /** @ignore */
        protected $_bundle_cache_changed = false;
        
        /** @ignore */
        protected $_data_source_bundle_cache;
        
        /** @ignore */
        protected $_data_source_bundle_cache_changed = false;
        
        /** @ignore */
        protected $_bundle_class_paths;
        
        /** @ignore */
        protected $_global_settings;
        
        /** @ignore */
        protected $_global_settings_changed = false;
        
        /** @ignore */
        protected $_repository;
        
        /**
         * Constructor
         */
        public function __constuct(){
            parent::__construct();
            $this->_global_settings = array();
        }
        
        /*
         * Properties
         */
        /** @ignore */
        public function pget_repository(){
            if ($this->_repository && $this->_repository instanceof repository){
                return $this->_repository;
            }else{
                $url = $this->setting('repository.url');
                $username = $this->setting('repository.username');
                $password = $this->setting('repository.password');
                $this->_repository = new repository($url, $username, $password);
                
                return $this->_repository;
            }
        }
        
        /**
         * Loads the global settings from settings.xml
         *
         * @access public
         */
        public function load_global_settings(){
            $this->_global_settings = $this->cache->get("adapt/global.settings");
            $this->_global_settings_changed = false;
            
            if (!is_array($this->_global_settings)){
                $this->_global_settings = array();
                
                $settings_path = ADAPT_PATH . "settings.xml";
                if (file_exists($settings_path)){
                    $settings_data = file_get_contents($settings_path);
                    
                    if ($settings_data && xml::is_xml($settings_data)){
                        $settings_data = xml::parse($settings_data);
                        
                        if ($settings_data instanceof xml){
                            $children = $settings_data->get(0)->get();
                            
                            foreach($children as $child){
                                
                                if ($child instanceof xml && strtolower($child->tag) == 'setting'){
                                    $items = $child->get();
                                    $name = null;
                                    $value = null;
                                    
                                    foreach($items as $item){
                                        if ($item instanceof xml){
                                            $tag = strtolower($item->tag);
                                            
                                            switch($tag){
                                            case "name":
                                                $name = $item->get(0);
                                                break;
                                            case "value":
                                                $value = $item->get(0);
                                                break;
                                            case "values":
                                                $value = array();
                                                $nodes = $item->get();
                                                foreach($nodes as $node){
                                                    if ($node instanceof xml){
                                                        if (strtolower($node->tag) == 'value'){
                                                            $value[] = $node->get(0);
                                                        }
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if (!is_null($name) && is_string($name) && strlen($name) > 0){
                                        $this->_global_settings[$name] = $value;
                                    }
                                }
                            }
                            
                            /* Cache the settings */
                            $this->cache->serialize("adapt/global.settings", $this->_global_settings, 60 * 60 * 24 * 5);
                        }
                    }
                }
            }
        }
        
        /**
         * Applies the global settings to the system
         *
         * @access public
         */
        public function apply_global_settings(){
            if (is_array($GLOBALS['__adapt']['settings'])){
                $GLOBALS['__adapt']['settings'] = array_merge($GLOBALS['__adapt']['settings'], $this->_global_settings);
            }else{
                $GLOBALS['__adapt']['settings'] = $this->_global_settings;
            }
        }
        
        /**
         * Saves the global settings to settings.xml
         *
         * @access public
         */
        public function save_global_settings(){
            if ($this->_global_settings_changed){
                $this->cache->serialize('adapt/global.settings', $this->_global_settings, 60 * 60 * 24 * 5);
                
                $settings = new xml_settings();
                foreach($this->_global_settings as $name => $value){
                    $setting = new xml_setting();
                    $setting->add(new xml_name($name));
                    
                    if (is_array($value)){
                        $values = new xml_values();
                        foreach($value as $val){
                            $values->add(new xml_value($val));
                        }
                        $setting->add($values);
                    }else{
                        $setting->add(new xml_value($value));
                    }
                    
                    $settings->add($setting);
                }
                
                $fp = fopen(ADAPT_PATH . "settings.xml", "w");
                if ($fp){
                    fwrite($fp, new xml_document('adapt_framework', $settings));
                    fclose($fp);
                    $this->_global_settings_changed = false;
                }else{
                    $this->error("Error saving " . ADAPT_PATH . "settings.xml");
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Gets a global setting from settings.xml
         *
         * @access public
         * @param string $key
         * The key to seek the value for
         * @return string
         */
        public function get_global_setting($key){
            if (isset($this->_global_settings[$key])){
                return $this->_global_settings[$key];
            }
            
            return null;
        }
        
        /**
         * Sets a global setting
         *
         * @access public
         * @param string key
         * The name of the setting
         * @param string $value
         * The value of the setting.
         */
        public function set_global_setting($key, $value){
            if ($this->get_global_setting($key) != $value){
                $this->_global_settings[$key] = $value;
                $this->_global_settings_changed = true;
            }
        }
        
        /**
         * Returns an array of all global settings
         * 
         * @return array
         */
        public function get_global_settings(){
            return $this->_global_settings;
        }
        
        /**
         * Allows the replacing of all global settings
         *
         * @access public
         * @param array $hash
         * Hash array containing the settings name and value.
         */
        public function set_global_settings($hash){
            $this->_global_settings = $hash;
            $this->_global_settings_changed = true;
        }
        
        /**
         * Boots an application.
         *
         * When $application_name is missing Adapt will seek the value
         * of 'adapt.default_application_name' from the global settings.
         *
         * If the setting 'adapt.default_application_name' is missing then
         * the first application found will be booted.
         *
         * @access public
         * @param string $application_name
         * Optional.  The application name to be booted.
         * @param string $application_version
         * Optional.  The version of the application to be booted.
         * @return boolean
         * Indicates if the application was successfully booted, or not.
         */
        public function boot_application($application_name = null, $application_version = null){
            /* Load global settings */
            $this->load_global_settings();
            
            /* Apply global settings */
            $this->apply_global_settings();
            
            if (is_null($application_name)){
                /* Do we have an application listed in settings? */
                $name = $this->get_global_setting('adapt.default_application_name');
                $version = $this->get_global_setting('adapt.default_application_version');
                
                if (isset($name) && strlen($name) > 0){
                    $application_name = $name;
                    $application_version = $version;
                }else{
                    /* Lets find the first application on the system */
                    $bundles = self::list_bundles();
                    foreach($bundles as $bundle){
                        $versions = self::list_bundle_versions($bundle);
                        $path = ADAPT_PATH . "{$bundle}/{$bundle}-{$versions[0]}/bundle.xml";
                        
                        if (file_exists($path)){
                            $bundle_data = file_get_contents($path);
                            if ($bundle_data && strpos($bundle_data, "<type>application</type>")){
                                $application_name = $bundle;
                                $version = self::get_newest_version($versions);
                                $application_version = $version;
                                
                                /* Store in global settings */
                                $this->set_global_setting("adapt.default_application_name", $application_name);
                                $this->set_global_setting("adapt.default_application_version", $application_version);
                                break;
                            }
                        }
                    }
                }
                
                /* If we don't have a valid application at this point
                 * then we are going to have to bail
                 */
                if (is_null($application_name)){
                    $this->error("Unable to find a valid application to boot.");
                    return false;
                }else{
                    /* Connect any data sources we have */
                    $drivers = $this->get_global_setting('datasource.driver');
                    $hosts = $this->get_global_setting('datasource.host');
                    $ports = $this->get_global_setting('datasource.port');
                    $usernames = $this->get_global_setting('datasource.username');
                    $passwords = $this->get_global_setting('datasource.password');
                    $schemas = $this->get_global_setting('datasource.schema');
                    $writables = $this->get_global_setting('datasource.writable');
                    
                    if (is_array($drivers) && is_array($hosts) && is_array($schemas)
                        && is_array($usernames) && is_array($passwords) && is_array($writables)
                        && count($drivers) == count($hosts) && count($drivers) == count($schemas)
                        && count($drivers) == count($usernames) && count($drivers) == count($passwords)
                        && count($drivers) == count($writables)){
                        
                        for($i = 0; $i < count($drivers); $i++){
                            if (class_exists($drivers[$i])){
                                if (isset($this->data_source)){
                                    /* Connect a new host */
                                    if ($this->data_source instanceof $drivers[$i]){
                                        $this->data_source->add($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $ports[$i], $writables[$i] == 'Yes' ? false : true);
                                    }
                                }else{
                                    /* Create a new data source */
                                    $driver = $drivers[$i];
                                    $this->data_source = new $driver($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $ports[$i], $writables[$i] == 'Yes' ? false : true);
                                    if (!$this->data_source instanceof $driver){
                                        $errors = $this->data_source->errors(true);
                                        
                                        foreach($errors as $error){
                                            $this->error("Database error: {$error}");
                                        }
                                    }
                                }
                            }
                        }
                        
                    }else{
                        $this->error('Unable to connect to the database, the data source settings in settings.xml are not valid.');
                    }
                    
//                    $updates = self::download_updates();
                    
                    /* Load the application */
                    $application = $this->load_bundle($application_name, $application_version);
                    
                    if ($application instanceof bundle && $application->is_loaded){
                        //Maybe impletement boot statergies here, if (we have one){...}
                        $cache_key = "dependencies-resolved-{$application->name}-{$application->version}";
                        $dependencies_resolved = $this->cache->get($cache_key);
                        
                        if (!$dependencies_resolved){
                            $dependencies_resolved = $this->has_all_dependencies($application->name, $application->version);
                            $in_error = false;

                            while(!$in_error && is_array($dependencies_resolved)){
                                
                                /* Fetch from the repository */
                                foreach($dependencies_resolved as $name => $versions){
                                    $version = self::get_newest_version($versions);
                                    if (!$this->fetch_bundle($name, $version)){
                                        $in_error = true;
                                    }
                                }
                                
                                if (!$in_error){
                                    $dependencies_resolved = $this->has_all_dependencies($application->name, $application->version);
                                }
                            }
                        }
                        
                        if ($dependencies_resolved){
                            $this->cache->set($cache_key, 1, 60 * 60 * 24 * 365);
                            /* Set the running application for the auto loader */
                            $this->setting("adapt.running_application", $application->namespace);
                            
                            /* Boot the application */
                            if ($application->boot()){
                                return true;
                            }else{
                                $errors = $application->errors(true);
                                foreach($errors as $error) $this->error($error);
                            }
                            
                        }else{
                            $this->error("Unable to boot system, dependencies couldn't be resolved");
                            return false;
                        }
                        
                    }else{
                        $this->error("Unable to find an application to boot :(");
                        
                        return false;
                    }
                    
                }
            }
        }
        
        /**
         * Fetch a bundle from the Adapt repository.
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle to fetch from the repository
         * @param string $bundle_version
         * Optional. The version of the bundle to fetch from the
         * repository.
         * @return boolean
         */
        public function fetch_bundle($bundle_name, $bundle_version = null){
            if ($bundle_version = $this->repository->has($bundle_name, $bundle_version)){
                if ($this->repository->get($bundle_name, $bundle_version) !== false){
                    return true;
                }else{
                    $errors = $this->repository->errors(true);
                    foreach($errors as $error){
                        $this->error($error);
                    }
                    
                    return false;
                }
            }else{
                $errors = $this->repository->errors(true);
                foreach($errors as $error){
                    $this->error($error);
                }
                
                return false;
            }
        }
        
        /**
         * Marks a bundle as installed
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle to mark as installed.
         * @param string $bundle_version
         * The version of the bundle.
         * @return boolean
         */
        public function set_bundle_installed($bundle_name, $bundle_version){
            if ($this->data_source && $this->data_source instanceof data_source_sql){
                
                if (!is_array($this->_data_source_bundle_cache)){
                    $cache = $this->cache->get("adapt/data_source/bundle.cache");
                    
                    if (is_array($cache)) $this->_data_source_bundle_cache = $cache;
                }
                
                if (!is_array($this->_data_source_bundle_cache)){
                    $this->_data_source_bundle_cache = array();
                    
                    $results = $this
                        ->data_source
                        ->sql
                        ->select('*')
                        ->from('bundle_version')
                        ->where(
                            new sql_and(
                                new sql_cond('date_deleted', sql::IS, new sql_null()),
                                new sql_cond('installed', sql::EQUALS, sql::q('Yes'))
                            )
                        )
                        ->execute(0)
                        ->results();
                    
                    $this->_data_source_bundle_cache = $results;
                    
                    $this->_data_source_bundle_cache_changed = true;
                }
                
                if (is_array($this->_data_source_bundle_cache)){
                    foreach($this->_data_source_bundle_cache as $bundle){
                        if ($bundle['name'] == $bundle_name && $bundle['version'] == $bundle_version){
                            return true;
                        }
                    }
                    
                    $this->_data_source_bundle_cache[] = array('name' => $bundle_name, 'version' => $bundle_version);
                    $this->_data_source_bundle_cache_changed = true;
                    $this->_bundle_cache_changed = true; //Because the the bundle->_is_installed has changed
                    
                    return true;
                }
                
                
            }
            
            return false;
        }
        
        /**
         * Is a bundle installed?
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle
         * @param string $bundle_version
         * The version of the bundle.
         * @return boolean
         */
        public function is_bundle_installed($bundle_name, $bundle_version){
            if ($this->data_source && $this->data_source instanceof data_source_sql){
                
                if (!is_array($this->_data_source_bundle_cache)){
                    $cache = $this->cache->get("adapt/data_source/bundle.cache");
                    
                    if (is_array($cache)) $this->_data_source_bundle_cache = $cache;
                }
                
                if (!is_array($this->_data_source_bundle_cache)){
                    $this->_data_source_bundle_cache = array();
                    
                    $results = $this
                        ->data_source
                        ->sql
                        ->select('*')
                        ->from('bundle_version')
                        ->where(
                            new sql_and(
                                new sql_cond('date_deleted', sql::IS, new sql_null()),
                                new sql_cond('installed', sql::EQUALS, sql::q('Yes'))
                            )
                        )
                        ->execute(0)
                        ->results();
                    
                    
                    $this->_data_source_bundle_cache = $results;
                    
                    $this->_data_source_bundle_cache_changed = true;
                }
                
                if (is_array($this->_data_source_bundle_cache)){
                    foreach($this->_data_source_bundle_cache as $bundle){
                        if ($bundle['name'] == $bundle_name && $bundle['version'] == $bundle_version){
                            return true;
                        }
                    }
                    
                }
                return false;
            }
            return false;
        }
        
        /**
         * Is a bundle available locally?
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle
         * @param string $bundle_version
         * Optional. The version of the bundle.
         * @return boolean
         */
        public function has_bundle($bundle_name, $bundle_version = null){
            if (in_array($bundle_name, $this->list_bundles())){
                if (is_null($bundle_version)){
                    return true;
                }else{
                    $versions = $this->list_bundle_versions($bundle_name);
                    foreach($versions as $version){
                        if (self::matches_version($bundle_version, $version)){
                            return true;
                        }
                    }
                }
            }
            
            return false;
        }
        
        /**
         * Have we met all the dependencies for a bundle?
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle
         * @param string $bundle_version
         * Optional. The version of the bundle.
         * @return boolean|array
         * Returns true or false, or an array of un-met dependencies.
         */
        public function has_all_dependencies($bundle_name, $bundle_version = null){
            $required_dependencies = array();
            $bundle = $this->load_bundle($bundle_name, $bundle_version);
            
            if ($bundle->is_loaded){
                $dependencies = $bundle->depends_on;
                
                if ($dependencies && is_array($dependencies)){
                    foreach($dependencies as $name => $versions){
                        $version = self::get_newest_version($versions);
                        
                        if ($this->has_bundle($name, $version)){
                            $output = $this->has_all_dependencies($name, $version);
                            if ($output === false){
                                return false;
                            }elseif(is_array($output)){
                                $required_dependencies = array_merge($required_dependencies, $output);
                            }
                        }else{
                            if (isset($required_dependencies[$bundle_name]) && !is_array($required_dependencies[$bundle_name])){
                                $required_dependencies[$name] = array($version);
                            }else{
                                $required_dependencies[$name][] = $version;
                            }
                        }
                    }
                }
                
            }else{
                $this->error("Unable to load bundle {$bundle_name}-{$bundle_version}");
                return false;
            }
            
            
            if (count($required_dependencies) == 0){
                return true;
            }
            
            return $required_dependencies;
        }
        
        /**
         * Returns a list of dependencies for a bundle.
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle
         * @param string $bundle_version
         * The version of the bundle.
         * @return array|boolean
         * Returns an array of dependencies.
         */
        public function get_dependency_list($bundle_name, $bundle_version){
            $cache_key = "adapt/dependency.list.{$bundle_name}-{$bundle_version}";
            if (is_null($bundle_version) || $bundle_version == ""){
                $cache_key = "adapt/dependency.list.{$bundle_name}";
            }
            
            $list = $this->cache->get($cache_key);
            if (!is_array($list)){
                $list = array();
                $required_bundles = $this->has_all_dependencies($bundle_name, $bundle_version);
                if ($required_bundles === true){
                    $bundle = $this->load_bundle($bundle_name, $bundle_version);
                    
                    if ($bundle->is_loaded){
                        
                        $dependencies = $bundle->depends_on;
                        
                        foreach($dependencies as $name => $versions){
                            
                            $version = self::get_newest_version($versions);
                            
                            $list[] = array(
                                'name' => $name,
                                'version' => $version
                            );
                            
                            $output = $this->get_dependency_list($name, $version);
                            foreach($output as $item){
                                $found = false;
                                foreach($list as $list_item){
                                    
                                    if ($list['name'] == $list_item['name']){
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if (!$found){
                                    $list[] = array(
                                        'name' => $item['name'],
                                        'version' => $item['version']
                                    );
                                }
                            }
                        }
                        
                        /* Clean the list */
                        $final = array();
                        
                        $list = array_reverse($list);
                        foreach($list as $item){
                            if (in_array($item['name'], array_keys($final))){
                                $final[$item['name']][] = $item['version'];
                            }else{
                                $final[$item['name']] = array($item['version']);
                            }
                        }
                        
                        $final = array_reverse($final);
                        $list = array();
                        
                        foreach($final as $bname => $versions){
                            $list[] = array(
                                'name' => $bname,
                                'version' => self::get_newest_version($versions)
                            );
                        }
                        
                        $this->cache->serialize($cache_key, $list, rand(60 * 60 * 24 * 5, 60 * 60 * 24 * 10)); // Between 5 and 10 days
                        
                    }else{
                        $this->error("Unable to load bundle {$bundle_name}-{$bundle_version}");
                        return false;
                    }
                }else{
                    
                    foreach($required_bundles as $required_bundle => $required_versions){
                        $this->error("Unable to find {$required_bundle}");
                    }
                    return false;
                }
            }
            
            return $list;
        }
        
        /**
         * Loads a bundle
         *
         * This is the preferred way to load a bundle because the function
         * caches and so performs much better than:
         *
         * <code>
         * //The slow way
         * $forms_bundle = new bundle_forms();
         * </code>
         *
         * Because \adapt\base exposes this class as a shared property you can
         * load a bundle from almost any class. In the following example we
         * will load the forms bundle from a html div element.
         *
         * <code>
         * $div = new html_div();
         * $forms_bundle = $div->bundles->load_bundle('forms');
         * </code>
         * 
         * 
         * @access public
         * @param string $bundle_name
         * The name of the bundle
         * @param string $bundle_version
         * Optionally. The version of the bundle.
         * @return bundle|boolean
         * Returns a bundle.
         */
        public function load_bundle($bundle_name, $bundle_version = null){
            if (!is_array($this->_bundle_cache)){
                $data = $this->cache->get("adapt/bundle_objects");
                
                if (is_array($data)){
                    $this->_bundle_class_paths = $data['paths'];
                    if (is_array($this->_bundle_class_paths)){
                        foreach($this->_bundle_class_paths as $path){
                            require_once($path);
                        }
                    }
                    
                    $this->_bundle_cache = unserialize($data['objects']);
                }
            }
            
            if (is_array($this->_bundle_cache)){
                
                foreach($this->_bundle_cache as $bundle => $versions){
                    if ($bundle_name == $bundle){
                        
                        $version_keys = array_keys($versions);
                        if (is_null($bundle_version)){
                            $version = self::get_newest_version($version_keys);
                            $bundle = $versions[$version];
                            $this->register_namespace($bundle->namespace, $bundle_name, $version);
                            return $bundle;
                        }else{
                            $matched_versions = array();
                            foreach($version_keys as $version){
                                if (self::matches_version($bundle_version, $version)){
                                    $matched_versions[] = $version;
                                }
                            }
                            
                            $version = self::get_newest_version($matched_versions);
                            if ($version){
                                $bundle = $versions[$version];
                                $this->register_namespace($bundle->namespace, $bundle_name, $version);
                                return $bundle;
                            }
                        }
                        
                        break;
                    }
                }
                
            }else{
                $this->_bundle_cache = array();
                $this->_bundle_class_paths = array();
            }
            
            /* Couldn't read from the cache so lets load manually */
            $available_versions = self::list_bundle_versions($bundle_name);
            $selected_version = null;
            
            if (is_null($bundle_version)){
                $selected_version = self::get_newest_version($available_versions);
            }else{
                $matched_versions = array();
                foreach($available_versions as $version){
                    if (self::matches_version($bundle_version, $version)){
                        $matched_versions[] = $version;
                    }
                }
                
                $selected_version = self::get_newest_version($matched_versions);
            }
            
            if (is_null($selected_version)){
                $this->error("Unable to find a valid version for bundle '{$bundle_name}'");
                return false;
            }else{
                /* We need to register the bundles namespace */
                $namespace = null;
                
                /* Parse the bundle.xml */
                $bundle_path = ADAPT_PATH . "{$bundle_name}/{$bundle_name}-{$selected_version}/bundle.xml";
                
                $bundle_data = file_get_contents($bundle_path);
                
                if ($bundle_data /*&& xml::is_xml($bundle_data)*/){
                    
                    $data = xml::parse($bundle_data);
                    
                    $namespace = $data->find('bundle')->find('namespace')->get(0);
                    if ($namespace instanceof xml) $namespace = $namespace->get(0);
                    
                    /* Record the bundle path if there is one to aid auto_loading later */
                    $bundle_class_path = ADAPT_PATH . "{$bundle_name}/{$bundle_name}-{$selected_version}/classes/bundle_{$bundle_name}.php";
                    if (file_exists($bundle_class_path)){
                        $this->_bundle_class_paths[] = $bundle_class_path;
                    }
                    
                    $this->register_namespace($namespace, $bundle_name, $selected_version);
                    
                    $class = "{$namespace}\\bundle_{$bundle_name}";
                    
                    $object = new $class($data);
                    if ($object && $object instanceof bundle){
                        $this->_bundle_cache[$bundle_name][$selected_version] = $object;
                        $this->_bundle_cache_changed = true;
                        
                        return $object;
                    }else{
                        $this->error("Failed to load bundle {$bundle_name} v{$selected_version}");
                        return false;
                    }
                    
                    
                }else{
                    $this->error("Unable to load {$bundle_path}");
                    return false;
                }
                
            }
        }
        
        /**
         * Saves the internal bundle cache for faster
         * performance in the future.
         *
         * @access public
         */
        public function save_bundle_cache(){
            if ($this->_bundle_cache_changed){
                $this->_bundle_cache_changed = false;
                //$this->cache->serialize("adapt/namespaces", $this->store('adapt.namespaces'), 60 * 60 * 24 * 5);
                $this->cache->serialize("adapt/bundle_objects", array('paths' => $this->_bundle_class_paths, 'objects' => serialize($this->_bundle_cache)), 60 * 60 * 24 * 5);
            }
            
            if ($this->_data_source_bundle_cache_changed){
                $this->_data_source_bundle_cache_changed = false;
                $this->cache->serialize("adapt/data_source/bundle.cache", $this->_data_source_bundle_cache, 60 * 60 * 24 * 3);
            }
        }
        
        /**
         * Checks the repository for updates and application upgrades
         * 
         * @return boolean
         * Indicates that updates are available
         */
        public function check_for_updates(){
            $checked = [];
            if ($this->data_source && $this->data_source instanceof data_source_sql){
                $sql = $this->data_source->sql;
                $sql->select('name', 'version', 'type')->from('bundle_version')->where(new sql_cond('date_deleted', sql::IS, new sql_null()));
                $results = $sql->execute()->results();
                foreach($results as $result){
                    list($major, $minor, $revision) = explode(".", $result['version']);
                    $version = "{$major}.{$minor}";
                    $array_key = "{$result['name']}-{$version}";
                    if (!in_array($array_key, $checked)){
                        if($result['type'] == 'application'){
                            $latest_version = $this->repository->has($result['name']);
                            if (self::get_newest_version($result['version'], $latest_version) != $result['version']){
                                // Add the bundle to the bundle version table
                                $model = new model_bundle_version();
                                $model->name = $result['name'];
                                $model->version = $latest_version;
                                $model->type = $result['type'];
                                $model->local = 'No';
                                $model->installed = 'No';
                                if ($model->save()){
                                    $checked[] = $array_key;
                                }
                            }
                        }
                        // Updating revisions
                        $latest_revision = $this->repository->has($result['name'], $version);
                        //  print "latest {$result['name']} revision {$latest_revision} \n";
                        if (self::matches_version($result['version'], $latest_revision)){
                            if (self::get_newest_version($result['version'], $latest_revision) != $result['version'] ){
                                print self::get_newest_version($result['version'], $latest_revision);
                                // Add the bundle to the bundle version table
                                $model = new model_bundle_version();
                                $model->name = $result['name'];
                                $model->version = $latest_revision;
                                $model->type = $result['type'];
                                $model->local = 'No';
                                $model->installed = 'No';
                                if ($model->save()){
                                    $checked[] = $array_key;
                                }
                            }
                        }
                    }
                }
            }
            if(count($checked)){
                return $checked;
            }else{
                return false;
            }
        }
        
        
        /**
         * Downloads updates
         * This method downloads new versions listed
         * in bundle_version.
         * 
         * @return boolean
         * Indicates that updates have been downloaded
         */
        public function download_updates(){
            $updates = self::check_for_updates();
            if($updates){
                $paths = [];
                foreach ($updates as $update){
                    $bundle = explode('-', $update);
                    $path = $this->repository->get($bundle[0],$bundle[1],false);
                    if($path){
                        $model = new model_bundle_version();
                        $model->load_by_name_and_version($bundle[0],$bundle[1]);
                        $model->local = Yes;
                        if($model->save()){
                            $paths[] = $path;
                        }
                    }
                    
                }
                
            }
            return false;
        }
        
        /**
         * Updates the system
         * Applies any updates that have been downloaded,
         * this method only updates revisions and does not
         * upgrade to new major or minor versions.
         */
        public function update_system(){
            
        }
        
        /**
         * Upgrades an application to a newer version
         * 
         * @param string
         * The name of the application to be upgraded
         * @param string
         * The version to upgrade too.
         * @return boolean
         * If the update succeeded
         */
        public function upgrade_application($application_name, $application_version){
            
        }
        
        /**
         * Static function to list all the bundles
         * available locally.
         *
         * @access public
         * @return string[]
         * Returns the bundle names of the local bundles.
         */
        public static function list_bundles(){
            $output = array();
            
            $bundles = scandir(ADAPT_PATH);
            
            foreach($bundles as $bundle){
                if (substr($bundle, 0, 1) != "." && $bundle != "store" && $bundle != "settings.xml") $output[] = $bundle;
            }
            
            return $output;
        }
        
        /**
         * Static function to list bundle versions available locally
         * for the named bundle.
         *
         * @access public
         * @param string $bundle_name
         * @return string[]
         */
        public static function list_bundle_versions($bundle_name){
            $output = array();
            
            $bundles = scandir(ADAPT_PATH . $bundle_name . "/");
            
            foreach($bundles as $bundle){
                if (substr($bundle, 0, 1) != "."){
                    list($bundle, $version) = explode("-", $bundle);
                    $output[] = $version;
                }
            }
            
            return $output;
        }
        
        /**
         * Registers a namespace.
         *
         * For Adapt to auto load on behalf of bundles it needs
         * to know the namespaces used by a bundle.
         *
         * @access public
         * @param string $namespace
         * The namespace to be registered
         * @param string $bundle_name
         * The bundle using the namespace
         * @param string $bundle_version
         * The version of the bundle using the namespace.
         */
        public function register_namespace($namespace, $bundle_name, $bundle_version){
            //print "<p>Registering namespace <strong>{$namespace}-{$bundle_version}</strong></p>"; //new html_p(array("Registering namespace ", new html_strong($namespace)));
            $namespaces = $this->store('adapt.namespaces');
            //if (!is_array($namespaces)){
            //    if ($this->cache && $this->cache instanceof cache){
            //        $namespaces = $this->cache->get('adapt/namespaces');
            //        
            //        if (!is_array($namespaces)){
            //            $namespaces = array();
            //        }
            //    }else{
            //        $namespaces = array();
            //    }
            //}
            
            $namespaces[$namespace] = array(
                'bundle_name' => $bundle_name,
                'bundle_version' => $bundle_version
            );
            
            $this->store('adapt.namespaces', $namespaces);
            
            //if ($this->cache && $this->cache instanceof cache){
            //    $this->cache->serialize('adapt.namespaces', $namespaces, 600);
            //}
        }
        
        /**
         * Static function to check if two versions are the
         * same.
         *
         * <code>
         * if (bundle::matches_version('2.0', '2.0.8')){
         *      //True because 2.0.8 is considered a revision of 2.0
         * }
         * </code>
         *
         * @access public
         * @param string $version_1
         * @param string $version_2
         * @return boolean
         */
        public static function matches_version($version_1, $version_2){
            $v1 = array('major' => '', 'minor' => '', 'revision' => '');
            $v2 = array('major' => '', 'minor' => '', 'revision' => '');

            $exploded1 = explode('.', $version_1);
            $exploded2 = explode('.', $version_2);

            $v1['major'] = isset($exploded1[0]) ? $exploded1[0] : '0';
            $v1['minor'] = isset($exploded1[1]) ? $exploded1[1] : '0';
            $v1['revision'] = isset($exploded1[2]) ? $exploded1[1] : '0';

            $v2['major'] = isset($exploded2[0]) ? $exploded2[0] : '0';
            $v2['minor'] = isset($exploded2[1]) ? $exploded2[1] : '0';
            $v2['revision'] = isset($exploded2[2]) ? $exploded2[1] : '0';
            
            if ($v1['major'] == $v2['major'] && $v1['minor'] == $v2['minor']){
                return true;
            }
            
            return false;
        }
        
        /**
         * Static function to get the newest version
         * from two or more.
         *
         * When both params are provided, each param is expected
         * to be a string.
         *
         * When only the first param is provided it is expected
         * to be an array of version strings.
         *
         * @access public
         * @param string|string[] $version_1
         * @param string $version_2
         * @return string
         * Returns the newest version string.
         */
        public static function get_newest_version($version_1, $version_2 = null){
            if (is_array($version_1)){
                
                if(count($version_1) == 1){
                    return $version_1[0];
                }elseif(count($version_1) > 1){
                    $selected_version = $version_1[0];
                    
                    for($i = 1; $i < count($version_1); $i++){
                        $selected_version = self::get_newest_version($selected_version, $version_1[$i]);
                    }
                    
                    return $selected_version;
                }
                
                return null;
                
            }else{
                $v1 = array('major' => '', 'minor' => '', 'revision' => '');
                $v2 = array('major' => '', 'minor' => '', 'revision' => '');
                
                $matches = array();
                
                if (preg_match_all("/^(\d+)(\.(\d+))?(\.(\d+))?$/", $version_1, $matches)){
                    $v1['major'] = $matches[1][0] == "" ? '0' : $matches[1][0];
                    $v1['minor'] = $matches[3][0] == "" ? '0' : $matches[3][0];
                    $v1['revision'] = $matches[5][0] == "" ? '0' : $matches[5][0];
                }
                
                if (preg_match_all("/^(\d+)(\.(\d+))?(\.(\d+))?$/", $version_2, $matches)){
                    $v2['major'] = $matches[1][0] == "" ? '0' : $matches[1][0];
                    $v2['minor'] = $matches[3][0] == "" ? '0' : $matches[3][0];
                    $v2['revision'] = $matches[5][0] == "" ? '0' : $matches[5][0];
                }
                
                if ($v1['major'] > $v2['major']){
                    return $version_1;
                }elseif($v1['major'] == $v2['major']){
                    if ($v1['minor'] > $v2['minor']){
                        return $version_1;
                    }elseif($v1['minor'] == $v2['minor']){
                        if ($v1['revision'] > $v2['revision']){
                            return $version_1;
                        }
                    }
                }
                
                return $version_2;
            }
        }
    }
}

