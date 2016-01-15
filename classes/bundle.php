<?php

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /*abstract*/ class bundle extends base{
        
        protected $_path;
        protected $_data;
        protected $_has_changed;
        protected $_is_loaded;
        protected $_model;
        protected $_booted;
        
        public function __construct($bundle_name, $bundle_version = null){
            parent::__construct();
            $this->_booted = false;
            //$this->_model = new model_bundle_version();
            $this->load($bundle_name, $bundle_version);
        }
        
        /*
         * Magic methods
         */
        public function __get($key){
            if ($this->_is_loaded && $this->_data instanceof xml){
                $children = $this->_data->find('bundle')->get(0);
                
                for($i = 0; $i < $children->count(); $i++){
                    $child = $children->get($i);
                    
                    if ($child instanceof xml){
                        if ($child->tag == $key){
                            return $child->get(0);
                        }
                    }
                }
            }
            return parent::__get($key);
        }
        
        public function __set($key, $value){
            if ($this->_is_loaded && $this->_data instanceof xml){
                $children = $this->_data->find('bundle')->get(0);
                
                for($i = 0; $i < $children->count(); $i++){
                    $child = $children->get($i);
                    
                    if ($child instanceof xml){
                        if ($child->tag == $key){
                            $current_value =  $child->get(0);
                            if ($value != $current_value){
                                $child->clear();
                                $child->add($value);
                                $this->_has_changed = true;
                                return true;
                            }
                        }
                    }
                }
            }
            return parent::__set($key, $value);
        }
        
        public function __toString(){
            if ($this->_is_loaded && $this->_data instanceof xml){
                return $_data;
            }
            
            return "";
        }
        
        /*
         * Properties
         */
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        public function pget_has_changed(){
            return $this->_has_changed;
        }
        
        public function pget_has_booted(){
            return $this->bundles->has_booted($this->name);
        }
        
        public function pget_is_installed(){
            if ($this->_model->installed == "Yes"){
                return true;
            }
            
            return false;
        }
        
        public function pget_path(){
            return $this->_path;
        }
        
        public function pget_version_major(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $major;
        }
        
        public function pset_version_major($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $major){
                $this->version = "{$value}.{$minor}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        public function pget_version_minor(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $minor;
        }
        
        public function pset_version_minor($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $minor){
                $this->version = "{$major}.{$value}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        public function pget_version_revision(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $revision;
        }
        
        public function pset_version_revision($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $revision){
                $this->version = "{$major}.{$minor}.{$value}";
                $this->_has_changed = true;
            }
        }
        
        /*
         * bundle.xml control
         */
        public function get_dependencies(){
            $out = array();
            
            if ($this->_is_loaded){
                $cache_key = "adapt.bundle." . $this->name . "." . $this->version . ".depends_on_array";
                $cached_copy = $this->cache->get($cache_key);
                if (is_array($cached_copy)){
                    
                    //print new html_pre("Cached dep list");
                    return $cached_copy;
                }else{
                    
                    $dependencies = $this->_data->find('depends_on > bundle')->get();
                    foreach($dependencies as $dependant){
                        //print $dependant;
                        if ($dependant instanceof xml && $dependant->tag == 'bundle'){
                            $children = $dependant->get();
                            $name = null;
                            
                            foreach($children as $child){
                                if ($child instanceof xml){
                                    switch($child->tag){
                                    case "name":
                                        $name = strval($child->get(0));
                                        $out[$name] = array();
                                        break;
                                    case "version":
                                        $value = strval($child->get(0));
                                        $out[$name][] = $value;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    $this->cache->serialize($cache_key, $out, 600);
                }
            }
            
            return $out;
        }

        
        public function load($bundle_name, $bundle_version = null){
            $this->_is_loaded = false;
            $this->_has_changed = false;
            $this->_data = null;
            
            /* Create a cache key, versioned if we are */
            $bundle_cache_key = "adapt.bundle." . $bundle_name;
            $path_cache_key = "adapt.bundle-path." . $bundle_name;
            
            if (!is_null($bundle_version)){
                $bundle_cache_key .= "-" . $bundle_version;
                $path_cache_key .= "-" . $bundle_version;
            }
            
            /* Is bundle.xml already cached? */
            $bundle_data = $this->cache->get($bundle_cache_key);
            $bundle_path = $this->cache->get($path_cache_key);
            
            if (is_null($bundle_data) || !$bundle_data instanceof xml || !is_string($bundle_path)){
                
                /* It's not cached so we need to read and parse */
                $bundle_path = ADAPT_PATH . $bundle_name . "/";
                
                if (is_dir($bundle_path)){
                    $available_versions = scandir($bundle_path);
                    
                    $selected_version = null;
                    
                    foreach($available_versions as $available_version){
                        $version_path = $bundle_path . $available_version . "/";
                        //print new html_pre("Version path: {$version_path}");
                        if (substr($available_version, 0, 1) != "." && is_dir($version_path)){
                            list($version_name, $version) = explode("-", $available_version);
                            //print new html_pre("Version: {$version}\nVersion name: {$version_name}\nBundle name: {$bundle_name}");
                            if ($version_name == $bundle_name && preg_match("/^\d+\.\d+\.\d+$/", $version)){
                                //print new html_pre("valid");
                                if (!is_null($bundle_version)){
                                    
                                    if (bundles::matches_version($bundle_version, $version)){
                                        if (is_null($selected_version)){
                                            $selected_version = $version;
                                        }else{
                                            //Is Newer?
                                            $selected_version = bundles::get_newest_version($selected_version, $version);
                                        }
                                    }
                                    
                                }else{
                                    if (is_null($selected_version)){
                                        $selected_version = $version;
                                    }else{
                                        //Is Newer?
                                        $selected_version = bundles::get_newest_version($selected_version, $version);
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!is_null($selected_version)){
                        $bundle_path .= "{$bundle_name}-{$selected_version}/";
                        
                        if (file_exists($bundle_path . "bundle.xml")){
                            
                            /* Read the file */
                            $bundle_data = file_get_contents($bundle_path . "bundle.xml");
                            
                            if ($bundle_data && xml::is_xml($bundle_data)){
                                $bundle_data = xml::parse($bundle_data);
                                
                                if ($bundle_data instanceof xml){
                                    /* Yay! */
                                    /* Cache the data */
                                    $this->cache->serialize($bundle_cache_key, $this->_data, 600);
                                    $this->cache->set($path_cache_key, $this->_path, 600, 'text/plain');
                                }else{
                                    $this->error("Unable to load bundle '{$bundle_name}' version '{$selected_version}', could not parse 'bundle.xml'.");
                                }
                            }else{
                                $this->error("Unable to load bundle '{$bundle_name}' version '{$selected_version}', could not read 'bundle.xml'.");
                                return false;
                            }
                            
                        }
                        
                    }else{
                        $this->error("Unable to load bundle '{$bundle_name}' due to the version required ({$bundle_version}) not being available");
                        return false;
                    }
                    
                }else{
                    $this->error("Unable to load bundle '{$bundle_name}' in '$bundle_path'.");
                    return false;
                }
                
                
            }
            
            $this->_data = $bundle_data;
            $this->_path = $bundle_path;
            $this->_is_loaded = true;
            
            
            
            return true;
        
            
            $this->_is_loaded = false;
            $this->_has_changed = false;
            $this->_data = null;
            //$this->_model->load_by_name($bundle_name);
            //print_r($this->cache);
            $cached_data = $this->cache->get('descriptor.bundle.' . $bundle_name);
            $cached_path = $this->cache->get('path.bundle.' . $bundle_name);
            if ($cached_data && $cached_data instanceof xml && is_string($cached_path)){
                $this->_data = $cached_data;
                $this->_path = $cached_path;
                return true;
            }else{
                $paths = array(
                    /*FRAMEWORK_PATH,
                    EXTENSION_PATH,
                    APPLICATION_PATH,
                    TEMPLATE_PATH*/
                    ADAPT_PATH
                );
                
                foreach($paths as $path){
                    if (in_array($bundle_name, scandir($path))){
                        if (file_exists($path . $bundle_name . "/bundle.xml")){
                            /* Read the bundle.xml */
                            $bundle_data = file_get_contents($path . $bundle_name . "/bundle.xml");
                            
                            if (xml::is_xml($bundle_data)){
                                
                                /* Store the path */
                                $this->_path = $path . $bundle_name . "/";
                                
                                /* Parse the data */
                                $bundle_data = xml::parse($bundle_data);
                                
                                if ($bundle_data instanceof xml){
                                    $this->_data = $bundle_data;
                                    
                                    /* Cache the data */
                                    $this->cache->serialize('descriptor.bundle.' . $bundle_name, $this->_data, 600);
                                    $this->cache->set('path.bundle.' . $bundle_name, $this->_path, 600, 'text/plain');
                                    
                                    /* Mark as loaded */
                                    $this->_is_loaded = true;
                                    
                                    return true;
                                }else{
                                    $this->error("Unable to parse '{$path}{$bundle_name}/bundle.xml'");
                                    return false;
                                }
                                
                            }else{
                                $this->error("Unable to read '{$path}{$bundle_name}/bundle.xml' it doesn't appear to be valid XML.");
                                return false;
                            }
                        }
                    }
                }
            }
            
            /* Load failed :/ */
            $this->error("Failed to locate the bundle.xml for bundle '{$bundle_name}'.");
            return false;
        }
        
        public function save(){
            if ($this->is_loaded){
                $this->_model->save();
                if ($this->_has_changed){
                    $fp = fopen($this->_path . "bundle.xml", "w");
                    if ($fp){
                        fwrite($fp, $this->data);
                        fclose($fp);
                        
                        $this->_is_loaded = true;
                        $this->_has_changed = true;
                        
                        $this->cache->serialize('descriptor.bundle.' . $this->name, $this->_data, 600);
                        $this->cache->set('path.bundle.' . $this->name, $this->_path, 600, 'text/plain');
                    }else{
                        $this->error('Unable to write {$this->_path}bundle.xml');
                        return false;
                    }
                }
                
                return true;
            }else{
                $this->error("Unable to create bundle.xml because it's just not allowed. You have to write it yourself, just because :p");
                return false;
            }
        }
        
        public function install(){
            if ($this->_is_loaded){
                /* Process config handlers */
                $handlers = $this->store('adapt.install_config_handlers');
                if ($handlers && is_array($handlers)){
                    
                    foreach($handlers as $key => $func){
                        $items = $this->_data->find($key);
                        
                        if ($items->size()){
                            $items = $items->get();
                            foreach($items as $item){
                                if ($item instanceof xml && $item->parent instanceof xml && $item->parent->tag == 'bundle'){
                                    $func($this, $item);
                                }
                            }
                        }
                    }
                }
                
                //$this->_model->installed = "Yes";
                //$this->_model->save();
                return true;
            }
            
            return false;
        }
        
        public function uninstall(){
            
        }
        
        public function boot($boot_dependencies = true){
            /* Process boot handlers */
            if ($this->_is_loaded){
                
                if ($this->has_booted){
                    return true;
                }else{
                    //print new html_h1("Booting {$this->name} {$this->version}");
                }
                
                /* Install before booting if needed */
                if (!$this->is_installed){
                    //$this->install();
                }
                
                $GLOBALS['time_offset'] = microtime(true);
                
                $handlers = $this->store('adapt.boot_config_handlers');
                if ($handlers && is_array($handlers)){
                    
                    foreach($handlers as $key => $func){
                        $items = $this->_data->find($key);
                        
                        if ($items->size()){
                            $items = $items->get();
                            foreach($items as $item){
                                if ($item instanceof xml && $item->parent instanceof xml && $item->parent->tag == 'bundle'){
                                    $func($this, $item);
                                }
                            }
                        }
                    }
                }
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to process boot handlers ({$this->name}): " . round($GLOBALS['time'], 4) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                
                
                /* Boot child bundles */
                if ($boot_dependencies){
                    $dependencies = $this->get_dependencies();
                    //print new html_pre("Depeneds on:" . print_r($dependencies, true));
                    //$GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                    //print "<pre>Time to find depencencies ({$this->name}): " . round($GLOBALS['time'], 3) . "</pre>";
                    //$GLOBALS['time_offset'] = microtime(true);
                    
                    foreach($dependencies as $name => $versions){
                        if (is_array($versions) && count($versions)){
                            $selected = $versions[0];
                            foreach($versions as $version){
                                $selected = bundles::get_newest_version($selected, $version);
                            }
                            
                            if ($selected == "0.0.0"){
                                $this->error("Unable to boot bundle '{$name}'");
                                return false;
                            }else{
                                $bundle = $this->bundles->get_bundle($name, $selected);
                                if ($bundle instanceof bundle && $bundle->is_loaded){
                                    if (!$bundle->boot()){
                                        $errors = $bundle->errors(true);
                                        foreach($errors as $error){
                                            $this->error($error);
                                        }
                                        
                                        return false;
                                    }
                                }else{
                                    $this->error("Unable to load bundle '{$name}'");
                                    return false;
                                }
                            }
                            
                        }else{
                            $bundle = $this->bundles->get_bundle($name);
                            if ($bundle instanceof bundle && $bundle->is_loaded){
                                if (!$bundle->boot()){
                                    $errors = $bundle->errors(true);
                                    foreach($errors as $error){
                                        $this->error($error);
                                    }
                                    
                                    return false;
                                }
                            }else{
                                $this->error("Unable to load bundle '{$name}'");
                                return false;
                            }
                        }
                    }
                }
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to process depencencies ({$this->name}): " . round($GLOBALS['time'], 3) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                
                $this->bundles->set_booted($this->name, $this->version);
                
                $GLOBALS['time'] = microtime(true) - $GLOBALS['time_offset'];
                print "<pre>Time to mark as booted ({$this->name}): " . round($GLOBALS['time'], 3) . "</pre>";
                $GLOBALS['time_offset'] = microtime(true);
                return true;
            }
            
            return false;
        }
        
        public function update(){
            
        }
        
        public function add_boot_config_handler($hook, $handler_function){
            $handlers = $this->store('adapt.boot_config_handlers');
            if (is_null($handlers)) $handlers = array();
            
            $handlers[$hook] = $handler_function;
            $this->store('adapt.boot_config_handlers', $handlers);
        }
        
        public function add_install_config_handler($hook, $handler_function){
            $handlers = $this->store('adapt.install_config_handlers');
            if (is_null($handlers)) $handlers = array();
            
            $handlers[$hook] = $handler_function;
            $this->store('adapt.install_config_handlers', $handlers);
        }
        
    }
    
    
}

?>