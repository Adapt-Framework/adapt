<?php

namespace frameworks\adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /*abstract*/ class bundle extends base{
        
        protected $_path;
        protected $_data;
        protected $_has_changed;
        protected $_is_loaded;
        
        public function __construct($bundle_name){
            parent::__construct();
            $this->load($bundle_name);
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
        
        /*
         * Properties
         */
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        public function pget_has_changed(){
            return $this->_has_changed;
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
                $dependencies = $this->_data->find('depends_on > bundle')->get();
                foreach($dependencies as $dependant){
                    print $dependant;
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
            }
            
            return $out;
        }

        
        public function load($bundle_name){
            $this->_is_loaded = false;
            $this->_has_changed = false;
            $this->_data = null;
            //print_r($this->cache);
            $cached_data = $this->cache->get('descriptor.bundle.' . $bundle_name);
            $cached_path = $this->cache->get('path.bundle.' . $bundle_name);
            if ($cached_data && $cached_data instanceof xml && is_string($cached_path)){
                $this->_data = $cached_data;
                $this->_path = $cached_path;
                return true;
            }else{
                $paths = array(
                    FRAMEWORK_PATH,
                    EXTENSION_PATH,
                    APPLICATION_PATH,
                    TEMPLATE_PATH
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
            }
        }
        
        public function boot(){
            /* Process boot handlers */
            if ($this->_is_loaded){
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
            }
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
        
        /* Static functions */
        public static function bundle($path){
            
        }
        
        public static function unbundle($bundle){
            
        }
    }
    
    
}

?>