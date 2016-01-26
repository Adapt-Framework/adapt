<?php

namespace adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class bundles extends base{
        
        protected $_global_settings = array();
        protected $_repository;
        //protected $_settings;
        protected $_has_changed;
        protected $_is_loaded;
        protected $_booted_bundles = array();
        
        /* The following is used create a boot order list
         * for the booting application so that it can be
         * cached and the in future executed without
         * any recurrsion.
         */
        protected $_boot_stratagies = array();
        protected $_booting_application = null;
        protected $_booting_application_version = null;
        
        public function __construct(){
            parent::__construct();
            //$this->_settings = new xml_document('adapt_framework', new xml_settings());
            $this->_is_loaded = false;
            $this->_has_changed = false;
        }
        
        /*
         * Properties
         */
        public function pget_repository(){
            if ($this->_repository && $this->_repository instanceof repository){
                return $this->_repository;
            }else{
                $username = $this->settings('repository.username');
                $password = $this->settings('repository.password');
                $this->_repository = new repository($username, $password);
                
                return $this->_repository;
            }
        }
        
        /*
         * Methods
         */
        public function create_boot_stratagy($bundle_name, $bundle_version){
            if (!is_null($this->_booting_application)){
                $stratergy = array();
                
                if (isset($this->_boot_stratagies[$this->_booting_application][$this->_booting_application_version])){
                    $stratergy = $this->_boot_stratagies[$this->_booting_application][$this->_booting_application_version];
                }
                
                if (!in_array($bundle_name, array_keys($stratergy))){
                    $stratergy[$bundle_name] = $bundle_version;
                }
                
                $this->_boot_stratagies[$this->_booting_application][$this->_booting_application_version] = $stratergy;
                
                //print new html_pre("Current stratergies: " . print_r($this->_boot_stratagies, true));
            }
        }
        
        public function has_booted($bundle_name){
            return in_array($bundle_name, $this->_booted_bundles);
        }
        
        public function set_booted($bundle_name, $bundle_version = null){
            $this->_booted_bundles[] = $bundle_name;
            
            /* Lets record this is the boot stratergy */
            $this->create_boot_stratagy($bundle_name, $bundle_version);
        }
        
        
        
        public function get_global_setting($key){
            return $this->_global_settings[$key];
        }
        
        public function set_global_setting($key, $value){
            if ($key && trim($key) != ""){
                $current_value = $this->get_global_setting($key);
                
                if ($current_value != $value){
                    $this->_has_changed = true;
                    $this->_global_settings[$key] = $value;
                }
            }
        }
        
        public function register_namespace($namespace, $bundle_name, $bundle_version){
            //print "<p>Registering namespace <strong>{$namespace}</strong></p>"; //new html_p(array("Registering namespace ", new html_strong($namespace)));
            $namespaces = $this->store('adapt.namespaces');
            if (!is_array($namespaces)){
                if ($this->cache && $this->cache instanceof cache){
                    $namespaces = $this->cache->get('adapt.namespaces');
                    
                    if (!is_array($namespaces)){
                        $namespaces = array();
                    }
                }else{
                    $namespaces = array();
                }
            }
            
            $namespaces[$namespace] = array(
                'bundle_name' => $bundle_name,
                'bundle_version' => $bundle_version
            );
            
            $this->store('adapt.namespaces', $namespaces);
            
            if ($this->cache && $this->cache instanceof cache){
                $this->cache->serialize('adapt.namespaces', $namespaces, 600);
            }
        }
        
        
        public function boot_system($application_name = null, $application_version = null){
            
            /* Load boot stratagies from the cache */
            $stratagies = $this->cache->get('adapt.boot_stratagies');
            //print new html_pre("Stratergies from cache" . print_r($stratagies, true));
            if (is_array($stratagies)){
                $this->_boot_stratagies = $stratagies;
            }
            
            $GLOBALS['time_offset'] = microtime(true);
            
            /* Load the global settings */
            $this->load_global_settings();
            
            $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
            print "<pre>Time to load settings (And pre boot): " . round($GLOBALS['time'], 4) . "</pre>";
            $GLOBALS['time_offset'] = microtime(true);
            
            /* Apply global settings */
            $this->apply_global_settings();
            
            
            $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
            print "<pre>Time to apply settings: " . round($GLOBALS['time'], 4) . "</pre>";
            $GLOBALS['time_offset'] = microtime(true);
            
            /* Find a valid application to boot */
            
            /* Has a default application been specified in settings? */
            if (is_null($application_name)){
                $application_name = $this->setting('adapt.default_application_name');
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to get application name from settings: " . round($GLOBALS['time'], 4) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                
                $application_version = $this->seting('adapt.default_application_version');
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to get application version: " . round($GLOBALS['time'], 4) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
            }
            
            /* Are there any applications locally? */
            if (is_null($application_name)){
                $applications = $this->list_local_applications();
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to list local applications: " . round($GLOBALS['time'], 4) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                
                if (count($applications)){
                    $application_name = $applications[0];
                }
                
                $this->set_global_setting('adapt.default_application_name', $application_name);
                $this->set_global_setting('adapt.default_application_version', $application_version);
                
            }
            
            /* Default to adapt_setup */
            if (is_null($application_name)){
                $application_name = 'adapt_setup';
                $this->set_global_setting('adapt.default_application_name', $application_name);
                $this->set_global_setting('adapt.default_application_version', null);
            }
            
            if (!is_null($application_name)){
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to find application candidate: " . round($GLOBALS['time'], 4) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                
                /* We can make some assumtions here, if we have a boot stratagy
                 * for this application then we can just assume we have everything
                 * and go ahead and just boot them one by one.
                 *
                 * We will need to connect the data source here first.
                 */
                
                if (isset($this->_boot_stratagies[$application_name][$application_version])){
                    
                    /* Connect the data source if we have one */
                    $drivers = $this->settings('data_source.driver');
                    $hosts = $this->settings('data_source.host');
                    $posts = $this->settings('data_source.port');
                    $usernames = $this->settings('data_source.username');
                    $passwords = $this->settings('data_source.password');
                    $schemas = $this->settings('data_source.schema');
                    $writables = $this->settings('data_source.writable');
                    
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
                                        $this->data_source->add($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
                                    }
                                }else{
                                    /* Create a new data source */
                                    $driver = $drivers[$i];
                                    $this->data_source = new $driver($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
                                }
                            }
                        }
                        
                    }else{
                        $this->error('Unable to connect to the data base, the data source settings in settings.xml are not valid.');
                    }
                    
                    
                    $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                    print "<pre>Time be ready to bundle boot: " . round($GLOBALS['time'], 4) . "</pre>";
                    $GLOBALS['time_offset'] = microtime(true);
                    
                    /* Boot the bundles one by one in order */
                    $stratagy = $this->_boot_stratagies[$application_name][$application_version];
                    
                    print new html_pre("Using stratergy: " . print_r($stratagy, true));
                    
                    foreach($stratagy as $bundle_name => $bundle_version){
                        $bundle = $this->get_bundle($bundle_name, $bundle_version);
                        
                        $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                        print "<pre>Time to load {$bundle->name}: " . round($GLOBALS['time'], 4) . "</pre>";
                        $GLOBALS['time_offset'] = microtime(true);
                        
                        $bundle->boot(false);
                        
                        $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                        print "<pre>Time to boot {$bundle->name}: " . round($GLOBALS['time'], 4) . "</pre>";
                        $GLOBALS['time_offset'] = microtime(true);
                    }
                    
                    /* We must return true */
                    return true;
                    
                }else{
                    
                    /* We don't have a boot stratagy so we are going to create one as we go */
                    $this->_booting_application = $application_name;
                    $this->_booting_application_version = $application_version;
                    
                    /* Is the bundle installed? */
                    if (!$this->has_bundle($application_name, $application_version)){
                        /* Does the repository have it? */
                        $this->fetch_from_respository($application_name, $application_version);
                        
                        //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                        //print "<pre>Time to fetch application: " . round($GLOBALS['time'], 3) . "</pre>";
                        //$GLOBALS['time_offset'] = microtime(true);
                    }
                    
                    if ($this->has_bundle($application_name, $application_version)){
                        
                        //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                        //print "<pre>Time to check if we have the application: " . round($GLOBALS['time'], 3) . "</pre>";
                        //$GLOBALS['time_offset'] = microtime(true);
                        
                        $this->get_dependencies($application_name, $application_version);
                        
                        //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                        //print "<pre>Time to get dependencies: " . round($GLOBALS['time'], 3) . "</pre>";
                        //$GLOBALS['time_offset'] = microtime(true);
                        
                        /* Save global settings if they've changed */
                        $this->save_global_settings();
                        
                        /* Connect the data source if we have one */
                        $drivers = $this->settings('data_source.driver');
                        $hosts = $this->settings('data_source.host');
                        $posts = $this->settings('data_source.port');
                        $usernames = $this->settings('data_source.username');
                        $passwords = $this->settings('data_source.password');
                        $schemas = $this->settings('data_source.schema');
                        $writables = $this->settings('data_source.writable');
                        
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
                                            $this->data_source->add($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
                                        }
                                    }else{
                                        /* Create a new data source */
                                        $driver = $drivers[$i];
                                        $this->data_source = new $driver($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
                                    }
                                }
                            }
                            
                        }else{
                            $this->error('Unable to connect to the data base, the data source settings in settings.xml are not valid.');
                        }
                        
                    }else{
                        $this->error("Unable to locate application '{$application_name}'");
                        return false;
                    }
                }
                
            }else{
                $this->error('Unable to find a vaild application to boot');
                return false;
            }
            
            ///* Save global settings if they've changed */
            //$this->save_global_settings();
            
            //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
            //print "<pre>Time to save settings: " . round($GLOBALS['time'], 3) . "</pre>";
            //$GLOBALS['time_offset'] = microtime(true);
            
            ///* Connect the data source if we have one */
            //$drivers = $this->settings('data_source.driver');
            //$hosts = $this->settings('data_source.host');
            //$posts = $this->settings('data_source.port');
            //$usernames = $this->settings('data_source.username');
            //$passwords = $this->settings('data_source.password');
            //$schemas = $this->settings('data_source.schema');
            //$writables = $this->settings('data_source.writable');
            //
            //if (is_array($drivers) && is_array($hosts) && is_array($schemas)
            //    && is_array($usernames) && is_array($passwords) && is_array($writables)
            //    && count($drivers) == count($hosts) && count($drivers) == count($schemas)
            //    && count($drivers) == count($usernames) && count($drivers) == count($passwords)
            //    && count($drivers) == count($writables)){
            //    
            //    for($i = 0; $i < count($drivers); $i++){
            //        if (class_exists($drivers[$i])){
            //            if (isset($this->data_source)){
            //                /* Connect a new host */
            //                if ($this->data_source instanceof $drivers[$i]){
            //                    $this->data_source->add($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
            //                }
            //            }else{
            //                /* Create a new data source */
            //                $driver = $drivers[$i];
            //                $this->data_source = new $driver($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], $writables[$i] == 'Yes' ? false : true);
            //            }
            //        }
            //    }
            //    
            //}else{
            //    $this->error('Unable to connect to the data base, the data source settings in settings.xml are not valid.');
            //}
            
            
            /* Boot the bundle */
            $bundle = $this->get_bundle($application_name, $application_version);
            
            //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
            //print "<pre>Time to get bundle: " . round($GLOBALS['time'], 3) . "</pre>";
            //$GLOBALS['time_offset'] = microtime(true);
            
            if ($bundle && $bundle instanceof bundle && $bundle->is_loaded){
                if ($bundle->boot()){
                    //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                    //print "<pre>Time to boot: " . round($GLOBALS['time'], 3) . "</pre>";
                    //$GLOBALS['time_offset'] = microtime(true);
                    
                    //print new html_pre("Final stratergy: " . print_r($this->_boot_stratagies, true));
                    
                    $this->cache->serialize('adapt.boot_stratagies', $this->_boot_stratagies, 600);
                    
                    return true;
                }else{
                    $errors = $bundle->errors(true);
                    foreach($errors as $error){
                        $this->error($error);
                    }
                    
                    return false;
                }
            }else{
                $this->error("Unable to load application '{$application_name}'");
                return false;
            }
        }
        
        public function get_bundle($bundle_name, $bundle_version = null){
            $cache_key = "adapt.bundle.get_bundle.{$bundle_name}";
            
            //print new html_pre("Request bundle = {$bundle_name} - {$bundle_version}");
            if (!is_null($bundle_version)){
                $cache_key .= "{$bundle_version}";
                if (!is_array($bundle_version)) $bundle_version = array($bundle_version);
            }
            
            $bundle = $this->cache->get($cache_key);
            if ($bundle instanceof bundle){
                //print new html_pre("Pulling from cache");
                return $bundle;
            }
            
            
            //print new html_pre("Requested versions: " . print_r($bundle_version, true));
            
            /* Check the cache */
            $cached_bundles = $this->store('adapt.bundles');
            
            if (is_array($cached_bundles)){
                //print new html_pre("Searching bundle cache...");
                foreach($cached_bundles as $name => $versions){
                    //print new html_pre("Checking againts {$name}");
                    if ($name == $bundle_name){
                        //print new html_pre("Names match");
                        if (is_array($bundle_version) && count($bundle_version)){
                            //print new html_pre("Has versions");
                            foreach($versions as $version => $bundle){
                                foreach($bundle_version as $v){
                                    if (self::matches_version($v, $bundle->version)){
                                        return $bundle;
                                    }
                                }
                            }
                            
                        }else{
                            /* Just return the first version we find */
                            //print new html_pre(print_r($versions, true));
                            $keys = array_keys($versions);
                            return $versions[$keys[0]];
                        }
                        
                    }
                }
            }
            
            /* If we get here then we have been unable to find the bundle in the cache
             * so we need to locate it and load it.  We should always load the most
             * recent version available to us unless bundle_version specifies
             * otherwise.
             */
            if ($this->has_bundle($bundle_name, $bundle_version)){
                $path = ADAPT_PATH . $bundle_name . "/";
                
                $available_versions = scandir($path);
                //print new html_pre("AV: " . print_r($available_versions, true));
                //print new html_pre("AV: " . print_r($bundle_version, true));
                
                $selected = "0.0.0";
                foreach($available_versions as $ver){
                    if (substr($ver, 0, 1) != "." && is_dir($path . $ver)){
                        list($version_name, $ver) = explode("-", $ver);
                        if (count($bundle_version)){
                            foreach($bundle_version as $bv){
                                if (self::matches_version($ver, $bv)){
                                    if ($selected == "0.0.0"){
                                        $selected = $ver;
                                    }else{
                                        $selected = self::get_newest_version($ver, $selected);
                                    }
                                }
                            }
                        }else{
                            if ($selected == "0.0.0"){
                                $selected = $ver;
                            }else{
                                $selected = self::get_newest_version($ver, $selected);
                            }
                        }
                    }
                }
                
                if ($selected == "0.0.0"){
                    $this->error("Adapt was unable to load the bundle '{$bundle_name}'.");
                    return false;
                }else{
                    /* We have a version we can use, we
                     * could just call \adapt\bundle::load(...)
                     * which will of course work, however, the bundle
                     * maybe providing it's own class named
                     * bundle_<bundle_name> which may including
                     * custom loading so we will have to call manually
                     * since the auto_loader 'voodoo' needs to a registered
                     * name space to load :/
                     * */
                    
                    /* Get the namespace from the bundle.xml */
                    $bundle_xml_file_path = ADAPT_PATH . $bundle_name . "/" . $bundle_name . "-" . $selected . "/bundle.xml";
                    if (file_exists($bundle_xml_file_path)){
                        
                        $xml_data = file_get_contents($bundle_xml_file_path);
                        if ($xml_data && xml::is_xml($xml_data)){
                            $xml_data = xml::parse($xml_data);
                            
                            if ($xml_data instanceof xml){
                                $namespace = $xml_data->find('namespace')->get(0)->get(0);
                                /* Register the namespace */
                                $this->register_namespace($namespace, $bundle_name, $selected);
                                
                                /* Get the bundle class */
                                $bundle_class = $namespace . "\\bundle_" . $bundle_name;
                                print new html_pre("Bundle class: {$bundle_class}");
                                $bundle = new $bundle_class();
                                
                                if ($bundle && $bundle instanceof bundle){
                                    /* Cache the bundle (This is a local runtime only cache) */
                                    $this->cache_bundle($bundle);
                                    
                                    /* Add it to the global cache */
                                    $this->cache->serialize($cache_key, $bundle, 600);
                                    
                                    /* Return the bundle */
                                    return $bundle;
                                }else{
                                    $this->error("Adapt was unable to load the bundle class '{$bundle_class}'");
                                    return false;
                                }
                                
                            }else{
                                $this->error("Adapt was unable to parse the bundle.xml for bundle '{$bundle_name}'.");
                                return false;
                            }
                        }else{
                            $this->error("Adapt was unable to load the bundle '{$bundle_name}', could not read bundle.xml.");
                            return false;
                        }
                        
                    }else{
                        $this->error("Adapt was unable to load the bundle '{$bundle_name}', could not read the bundle.xml file.");
                        return false;
                    }
                    
                }
                
            }else{
                $this->error("The bundle '{$bundle_name}' is not available locally, it maybe available in the Adapt Repository.");
                return false;
            }
            
        }
        
        public function cache_bundle($bundle){
            if ($bundle instanceof \adapt\bundle){
                $bundles = $this->store('adapt.bundles');
                
                //$key = $bundle->name . '-' . $bundle->version;
                $name = $bundle->name;
                $version = $bundle->version;
                if (!is_array($bundles[$name])){
                    $bundles[$name] = array();
                }
                $bundles[$name][$version] = $bundle;
                
                $this->store('adapt.bundles', $bundles);
            }
        }
        
        public function has_bundle($bundle_name, $bundle_version = null){
            if (!is_null($bundle_version)){
                if (!is_array($bundle_version)) $bundle_version = array($bundle_version);
            }
            
            //print new html_pre("bundles:has_bundle\n'{$bundle_name}'\n" . print_r($bundle_version, true));
            
            
            //Locally
            $bundle_list = $this->list_local_bundles();
            
            //print new html_pre("Bundle list: " . print_r($bundle_list, true));
            
            if (in_array($bundle_name, array_keys($bundle_list))){
                if (is_array($bundle_version) && count($bundle_version)){
                    foreach($bundle_list[$bundle_name] as $bv){
                        foreach($bundle_version as $version){
                            if ($this->matches_version($version, $bv)){
                                //print new html_pre("Returning true1");
                                return true;
                            }
                        }    
                    }
                }else{
                    //print new html_pre("Returning true2");
                    return true;
                }
            }
            //print new html_pre("Returning false3");
            return false;
        }
        
        public function list_local_bundles(){
            $output = array();
            $path = ADAPT_PATH;
            $list_files = scandir($path);
            
            foreach($list_files as $file){
                $full_path = $path . $file;
                
                if (substr($file, 0, 1) != '.'){
                    if (is_dir($full_path)){
                        $output[$file] = array();
                        
                        $list_versions = scandir($full_path);
                        foreach($list_versions as $v){
                            if (substr($v, 0, 1) != '.'){
                                if (is_dir($full_path . '/' . $v)){
                                    list($name, $ver) = explode("-", $v);
                                    $output[$file][] = $ver;
                                }
                            }
                        }
                    }
                }
            }
            
            return $output;
        }
        
        public function list_local_applications(){
            $output = array();
            
            /* Get a list of local bundles */
            $bundles = $this->list_local_bundles();
            
            foreach($bundles as $bundle_name => $bundle_versions){
                $file_path = ADAPT_PATH . $bundle_name . "/" . $bundle_name . "-" . $bundle_versions[0] . "/bundle.xml";
                
                if (file_exists($file_path)){
                    $file_content = file_get_contents($file_path);
                    
                    if (strpos($file_content, "<type>application</type>") !== false){
                        $output[] = $bundle_name;
                    }
                    
                    //if (xml::is_xml($file_content)){
                    //    $file_content = xml::parse($file_content);
                    //    
                    //    if ($file_content instanceof xml){
                    //        $type = $file_content->find('type')->get(0)->get(0);
                    //        if ($type == "application"){
                    //            $output[] = $bundle_name;
                    //        }
                    //    }
                    //}
                }
            }
            
            return $output;
        }
        
        public function what_works_with($bundle_name, $bundle_version = array()){
            
        }
        
        public function get_dependencies($bundle_name, $bundle_version = null){
            
            if (is_null($bundle_version)){
                if ($this->cache->get($bundle_name . "." . $bundle_verion . ".has_dependencies") == 'true'){
                    return true;
                }
            }else{
                if ($this->cache->get($bundle_name . ".has_dependencies") == 'true'){
                    return true;
                }
            }
            
            $bundle = $this->get_bundle($bundle_name, $bundle_version);
            
            if ($bundle && $bundle instanceof bundle && $bundle->is_loaded){
                $dependencies = $bundle->get_dependencies();
                //print new html_pre(print_r($dependencies, true));
                foreach($dependencies as $dependency_name => $dependency_versions){
                    
                    if (count($dependency_versions)){
                        $selected_version = null;
                        foreach($dependency_versions as $version){
                            if (is_null($selected_version)){
                                $selected_version = $version;
                            }else{
                                $selected_version = self::get_newest_version($selected_version, $version);
                            }
                        }
                        
                        if (is_null($selected_version)){
                            if (!$this->has_bundle($dependency_name)){
                                if (!$this->fetch_from_respository($dependency_name)){
                                    $this->error("Unable to fetch {$dependency_name}");
                                    return false;
                                }
                            }
                            
                            if ($this->has_bundle($dependency_name)){
                                if (!$this->get_dependencies($dependency_name)){
                                    return false;
                                }
                            }else{
                                $this->error("{$dependency_name} is unavailable");
                                return false;
                            }
                        }else{
                            if (!$this->has_bundle($dependency_name, $selected_version)){
                                if (!$this->fetch_from_respository($dependency_name, $selected_version)){
                                    $this->error("Unable to fetch {$dependency_name} {$selected_version}");
                                    return false;
                                }
                            }
                            
                            if ($this->has_bundle($dependency_name, $selected_version)){
                                if (!$this->get_dependencies($dependency_name, $selected_version)){
                                    return false;
                                }
                            }else{
                                $this->error("{$dependency_name} is unavailable");
                                return false;
                            }
                        }
                    }else{
                        /* We don't care about the version */
                        if (!$this->has_bundle($dependency_name)){
                            if (!$this->fetch_from_respository($dependency_name)){
                                $this->error("Unable to fetch {$dependency_name}");
                                return false;
                            }
                        }
                        
                        if ($this->has_bundle($dependency_name)){
                            if (!$this->get_dependencies($dependency_name)){
                                return false;
                            }
                        }else{
                            $this->error("{$dependency_name} is unavailable");
                            return false;
                        }
                    }
                }
            }else{
                $this->error("{$bundle_name} not found.");
                //print new html_pre(print_r($bundle, true));
                return false;
            }
            
            if (is_null($bundle_version)){
                $this->cache->set($bundle_name . "." . $bundle_verion . ".has_dependencies", 'true', 600);
            }else{
                $this->cache->set($bundle_name . ".has_dependencies", 'true', 600);
            }
            
            return true;
            
            $dependencies = $this->has_all_dependencies($bundle_name, $bundle_version);
            
            while($dependencies !== true){
                $child_dependencies = array();
                foreach($dependencies as $name => $versions){
                    //print new html_h1("Working with {$name}");
                    if (is_array($versions) && count($versions)){
                        $selected_version = "0.0.0";
                        
                        foreach($versions as $version){
                            if ($selected_version != "0.0.0"){
                                $selected_version = $version;
                            }else{
                                $selected_version = self::get_newest_version($selected_version, $version);
                            }
                        }
                        
                        if ($selected_version != "0.0.0"){
                            if (!$this->fetch_from_respository($name, $selected_version)){
                                $this->error("Unable to fetch '{$name}' (v{$selected_version}) from the repository");
                                return false;
                            }else{
                                if (!$this->get_dependencies($name, $selected_version)){
                                    return false;
                                }
                            }
                        }else{
                            if (!$this->fetch_from_respository($name)){
                                $this->error("Unable to fetch '{$name}' from the repository");
                                return false;
                            }else{
                                if (!$this->get_dependencies($name)){
                                    return false;
                                }
                            }
                        }
                        
                        
                    }else{
                        if (!$this->fetch_from_respository($name)){
                            $this->error("Unable to fetch '{$name}' from the repository");
                            return false;
                        }else{
                            if (!$this->get_dependencies($name, $selected_version)){
                                return false;
                            }
                        }
                    }
                    
                    //$child_dependencies = $this->has_all_dependencies($name, $bundle_version);
                    //if ($this->get_dependencies($name, $bund))
                }
                
                //if (is_array($child_dependencies)) $dependencies = array_merge($child_dependencies, $dependencies);
                
                
            }
            
            
            return true;
        }
        
        public function has_all_dependencies($bundle_name, $bundle_version = null){
            //print new html_h4("Checking dependencies for: {$bundle_name} {$bundle_version}");
            //return true or an array of bundles/versions required
            $output = array();
            
            $bundle = $this->get_bundle($bundle_name, $bundle_version);
            
            if ($bundle instanceof bundle && $bundle->is_loaded){
                $dependencies = $bundle->get_dependencies();
                
                foreach($dependencies as $name => $versions){
                    if (is_array($versions) && count($versions)){
                        $selected = "0.0.0";
                        foreach($versions as $version){
                            if ($this->has_bundle($name, $version)){
                                $selected = $version;
                                
                                $child_dependencies = $this->has_all_dependencies($name, $version);
                                if (is_array($child_dependencies)){
                                    $output = array_merge($output, $child_dependencies);
                                }
                            }
                        }
                        
                        if ($selected == "0.0.0"){
                            $output[$name] = $versions;
                        }
                        
                    }else{
                        if (!$this->has_bundle($name)){
                            $output[$name] = array();
                        }else{
                            $child_dependencies = $this->has_all_dependencies($name);
                            if (is_array($child_dependencies)){
                                $output = array_merge($output, $child_dependencies);
                            }
                        }
                    }
                }
            }
            
            if (count($output) == 0){
                return true;
            }
            
            return $output;
        }
        
        public function fetch_from_respository($bundle_name, $bundle_version = null){
            if ($this->repository->has($bundle_name, $bundle_version)){
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
        
        public function load_global_settings(){
            /*
             * We are going to load the global
             * settings and apply them to each bundle
             * before booting.
             */
            if (!$this->_is_loaded){
                $cached_settings = parent::aget_cache()->get('adapt.global_settings');
                
                if ($cached_settings && is_array($cached_settings)){
                    $this->_global_settings = $cached_settings;
                    $this->_has_changed = false;
                    $this->_is_loaded = true;
                    return true;
                }else{
                    if (file_exists(ADAPT_PATH . "settings.xml")){
                        $file_content = trim(file_get_contents(ADAPT_PATH . "settings.xml"));
                        
                        if ($file_content && strlen($file_content) > 0 && xml::is_xml($file_content)){
                            $settings = xml::parse($file_content);
                            
                            if ($settings instanceof xml){
                                $this->_is_loaded = true;
                                $this->_has_changed = true;
                                
                                
                                $children = $settings->get();
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
                                
                            }else{
                                $this->error("Unable to parse " . ADAPT_PATH . "settings.xml");
                                return false;
                            }
                        }else{
                            $this->error(ADAPT_PATH . "settings.xml doesn't appear to be valid xml so we completely ignored it (Well apart from this error message and the resulting false that follows).");
                            return false;
                        }
                    }
                }
            }
            
            return true;
        }
        
        public function save_global_settings(){
            if ($this->_has_changed){
                parent::aget_cache()->serialize('adapt.global_settings', $this->_global_settings, 120);
                
                $settings = new xml_settings();
                foreach($this->_global_settings as $name => $value){
                    $setting = new xml_setting();
                    $setting->add(new xml_name($name));
                    
                    if (is_array($value)){
                        $values = new xml_values();
                        foreach($values as $val){
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
                    $this->_has_changed = false;
                    $this->_is_loaded = true;
                }else{
                    $this->error("Error saving " . ADAPT_PATH . "settings.xml");
                    return false;
                }
            }
            
            return true;
        }
        
        public function apply_global_settings(){
            foreach($this->_global_settings as $key => $value){
                $this->setting($key, $value);
            }
        }
        
        
        
        
        
        public function bundle($bundle_name, $bundle_version){
            
        }
        
        public function unbundle($bundle_file_path){
            /*
             * We need to extract the manifest
             * to get the type and name.
             */
            $fp = fopen($bundle_file_path, "r");
            if ($fp){
                $raw_index = fgets($fp);
                $bundle_index = json_decode($raw_index, true);
                $offset = strlen($raw_index);
                
                if (is_array($bundle_index) && count($bundle_index)){
                    foreach($bundle_index as $file){
                        if ($file['name'] == 'bundle.xml'){
                            fseek($fp, $offset);
                            $manifest = fread($fp, $file['length']);
                        }
                        $offset += $file['length'];
                    }
                    
                    if (xml::is_xml($manifest)){
                        $manifest = xml::parse($manifest);
                        $name = $manifest->find('name');
                        $type = $manifest->find('type');
                        $version = $manifest->find('version');
                        $name = trim($name->get(0)->text);
                        $type = trim($type->get(0)->text);
                        $version = trim($version->get(0)->text);
                        //list($major, $minor, $revision) = explode(".", $version);
                        
                        if (in_array(strtolower($type), array('application', 'extension', 'framework'))){
                            /* Is this bundle already installed? */
                            if ($this->has_bundle($name, $version) === false){
                                /*
                                 * The bundle isn't installed so we are going
                                 * to unbundle it
                                 */
                                $path = ADAPT_PATH;
                                /*switch($type){
                                case 'application':
                                    $path = APPLICATION_PATH;
                                    break;
                                case 'extension':
                                    $path = EXTENSION_PATH;
                                    break;
                                case 'framework':
                                    $path = FRAMEWORK_PATH;
                                    break;
                                }*/
                                
                                mkdir($path . $name);
                                $path .= $name . '/';
                                
                                mkdir($path . $name . "-" . $version);
                                $path .= $name . '-' . $version . '/';
                                
                                /* Reset the bundle file point back to the start of the body */
                                fseek($fp, strlen($raw_index));
                                
                                /* Lets extract the bundle */
                                foreach($bundle_index as $file){
                                    $path_parts = explode('/', trim(dirname($file['name']), '/'));
                                    $new_path = $path;
                                    foreach($path_parts as $p){
                                        $new_path .= "/{$p}";
                                        if (!is_dir($new_path)){
                                            mkdir($new_path);
                                        }
                                    }
                                    
                                    $ofp = fopen($path . $file['name'], "w");
                                    
                                    if ($ofp){
                                        fwrite($ofp, fread($fp, $file['length']));
                                        fclose($ofp);
                                    }
                                }
                                
                                fclose($fp);
                                return $name;
                            }else{
                                return $name;
                            }
                        }
                        
                        
                    }else{
                        $this->error("Error unbundling {$bundle_file_path}, unable to read the manifest.");
                    }
                    
                }else{
                    $this->error("Error unbundling {$bundle_file_path}, unable to find the bundle index.");
                }
                
                fclose($fp);
            }else{
                $this->error("Error unbundling {$bundle_file_path}, unable to read the file.");
            }
            
            return false;
        }
        
        public static function matches_version($version_1, $version_2){
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
            
            if ($v1['major'] == $v2['major'] && $v1['minor'] == $v2['minor']){
                return true;
            }
            
            return false;
        }
        
        
        public static function get_newest_version($version_1, $version_2){
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

?>