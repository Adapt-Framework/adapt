<?php

namespace frameworks\adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class bundle extends base{
        
        protected $_name;
        protected $_manifest;
        protected $_path;
        protected $_booted = 'No';
        protected $_settings;
        
        public function __construct($name = null){
            parent::__construct();
            $this->_settings = array();
            
            if (!is_null($name)){
                $this->load($name);
            }
        }
        
        public function pget_is_loaded(){
            return isset($this->_name);
        }
        
        public function pget_path(){
            return $this->_path;
        }
        
        public function pget_name(){
            return $this->_manifest->find('name')->text();
        }
        
        public function pget_bundle_name(){
            return $this->_name;
        }
        
        public function pget_version(){
            return $this->_manifest->find('version')->text();
        }
        
        public function pget_installed(){
            if ($this->_manifest->find('installed')->text() == 'Yes'){
                return 'Yes';
            }else{
                return 'No';
            }
        }
        
        public function pget_booted(){
            return $this->_booted;
        }
        
        public function pset_booted($value){
            if (strtolower($value) == 'yes'){
                $this->_booted = 'Yes';
            }else{
                $this->_booted = 'No';
            }
        }
        
        public function install(){
            if ($this->is_loaded){
                if ($this->installed != 'Yes'){
                    $this->store('adapt.installing_bundle', $this->_name);
                    if (file_exists($this->path . "install.php")){
                        require_once($this->path . "install.php");
                    }
                    $this->store('adapt.installing_bundle', '');
                    //TODO: Set the installed flag and save the manifest
                    $this->_manifest->find('installed')->detach();
                    $this->_manifest->find('bundle')->append(new xml_installed('Yes'));
                    $this->write_manifest();
                }
            }
        }
        
        public function get_dependencies(){
            //print "IN: {$this->_name}->get_dependencies()\n";
            if ($this->is_loaded){
                //print "LOADED: {$this->_name}->get_dependencies()\n";
                $depends_on = $this->_manifest->find('depends_on bundle');
                $out = array();
                for($i = 0; $i < $depends_on->size(); $i++){
                    $out[] = trim($depends_on->get($i)->text);
                }
                return $out;
            }
            
            return array();
        }
        
        public function load_settings(){
            if ($this->is_loaded){
                if (file_exists($this->_path . "settings.xml")){
                    $settings_file = file_get_contents($this->_path . "settings.xml");
                    if (xml::is_xml($settings_file)){
                        $settings = xml::parse($settings_file);
                        if ($settings instanceof xml){
                            $pairs = $settings->find('setting')->get();
                            foreach($pairs as $pair){
                                $key = $pair->find('key')->text();
                                $value = null;
                                
                                if (count($pair->find('values')->get()) == 1){
                                    $value_tags = $pair->find('value')->get();
                                    $value = array();
                                    foreach($value_tags as $tag){
                                        $value[] = $tag->get(0);
                                    }
                                }else{
                                    $value = trim($pair->find('value')->get(0));
                                    if (strtolower($value) === "true") $value = true;
                                    if (strtolower($value) === "false") $value = false;
                                }
                                
                                /* Set the setting */
                                $this->setting($key, $value);
                            }
                        }
                    }
                }
            }
        }
        
        public function boot(){
            if ($this->booted == 'Yes') return true;
            
            $bundles = new bundles();
            
            if ($this->is_loaded){
                $dependencies = $this->get_dependencies();
                $load_count = 0;
                foreach($dependencies as $dep){
                    $bundle = $bundles->get_bundle($dep);
                    
                    if ($bundle instanceof bundle && $bundle->is_loaded){
                        if ($bundle->boot()){
                            $load_count++;
                        }
                    }
                }
                
                if ($load_count == count($dependencies)){
                    /*
                     * Load libraries
                     */
                    if (is_dir($this->path . "libraries")){
                        $files = scandir($this->path . "libraries", SCANDIR_SORT_ASCENDING);
                        
                        foreach($files as $file){
                            if (preg_match("/\.php$/", $file)){
                                require_once($this->path . "libraries/" . $file);
                            }
                        }
                    }
                    
                    /*
                     * Load config
                     */
                    if (is_dir($this->path . "config")){
                        $files = scandir($this->path . "config", SCANDIR_SORT_ASCENDING);
                        
                        foreach($files as $file){
                            if (preg_match("/\.php$/", $file)){
                                require_once($this->path . "config/" . $file);
                            }
                        }
                    }
                    
                    /*
                     * Load settings
                     */
                    $this->load_settings();
                    
                    /*
                     * Connect any datasources
                     */
                    $drivers = $this->setting('data_source.driver');
                    $hosts = $this->setting('data_source.host');
                    $schemas = $this->setting('data_source.schema');
                    $usernames = $this->setting('data_source.username');
                    $passwords = $this->setting('data_source.password');
                    $writables = $this->setting('data_source.writable');
                    
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
                                        $this->data_source->add($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], !$writables[$i]);
                                    }
                                }else{
                                    /* Create a new data source */
                                    $driver = $drivers[$i];
                                    $this->data_source = new $driver($hosts[$i], $usernames[$i], $passwords[$i], $schemas[$i], !$writables[$i]);
                                }
                            }
                        }
                        
                    }else{
                        $this->error('The data source setting are not valid.');
                    }
                    
                    if (file_exists($this->path . 'boot.php')){
                        require_once($this->path . 'boot.php');
                    }
                    
                    $this->booted = 'Yes';
                    return true;
                }
                
            }
            
            return false;
        }
        
        public function load($name){
            if ($this->load_manifest($name)){
                $this->_name = $name;
                return true;
            }
            
            return false;
        }
        
        
        protected function load_manifest($bundle_name){
            $paths = array(
                FRAMEWORK_PATH,
                EXTENSION_PATH,
                TEMPLATE_PATH,
                APPLICATION_PATH
            );
            
            
            foreach($paths as $path){
                if (is_dir($path . $bundle_name) || is_link($path . $bundle_name)){
                    if (file_exists($path . $bundle_name . "/manifest.xml")){
                        $manifest = file_get_contents($path . $bundle_name . "/manifest.xml");
                        if (!is_null($manifest) && $manifest != "" && xml::is_xml($manifest)){
                            $this->_manifest = xml::parse($manifest);
                            $this->_path = $path . $bundle_name . '/';
                            
                            /* We need to parse the settings from the manifest */
                            if ($this->_manifest instanceof xml){
                                $settings = $this->_manifest->find('setting')->get();
                                
                                foreach($settings as $setting){
                                    $key = $setting->find('key')->text();
                                    $this->_settings[$key] = array(
                                        'label' => $setting->find('label')->text(),
                                        'type' => $setting->find('type')->text()
                                    );
                                    
                                    if (strtolower($this->_settings[$key]['type']) == 'list'){
                                        $allowed_values = array();
                                        $values = $setting->find('value')->get();
                                        foreach($values as $value){
                                            $allowed_values[] = $value->get(0);
                                        }
                                        $this->_settings[$key]['allowed_values'] = $allowed_values;
                                    }
                                }
                                
                            }
                            
                            return true;
                        }
                    }
                }
            }
            
            return false;
        }
        
        protected function write_manifest($bundle_name){
            if ($this->_manifest instanceof xml){
                $paths = array(
                    FRAMEWORK_PATH,
                    EXTENSION_PATH,
                    TEMPLATE_PATH
                );
                
                if (defined('ACTIVE_APPLICATION_PATH')){
                    $paths[] = ACTIVE_APPLICATION_PATH;
                }
                
                foreach($paths as $path){
                    if (is_dir($path . $bundle_name)){
                        $fp = fopen($path . $bundle_name . '/manifest.xml', 'w');
                        if ($fp){
                            fwrite($fp, $this->_manifest);
                        }
                        fclose($fp);
                        return true;
                    }
                }
            }
            
            return false;
        }
        
    }
    
}

?>