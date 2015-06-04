<?php

namespace frameworks\adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class bundle extends base{
        
        protected $_descriptor;
        protected $_booted;
        protected $_path;
        
        
        public function __construct($bundle_name = null){
            parent::__construct();
            
            $this->_descriptor = array();
            $this->_booted = false;
            
            if (!is_null($bundle_name)){
                $this->load($bundle_name);
            }
        }
        
        /*
         * Properties
         */
        public function pget_is_loaded(){
            return count($this->_descriptor) ? true : false;
        }
        
        public function pget_name(){
            if ($this->is_loaded && isset($this->_descriptor['name'])){
                return $this->_descriptor['name'];
            }
            
            return null;
        }
        
        public function pget_booted(){
            return $this->_booted;
        }
        
        public function pget_depends_on(){
            if ($this->is_loaded && isset($this->_descriptor['depends_on'])){
                return $this->_descriptor['depends_on'];
            }
            
            return array();
        }
        
        public function pget_bundle_path(){
            return $this->_path;
        }
        
        /*
         * Functions
         */
        
        public function load($bundle_name){
            $this->_descriptor = array();
            
            $paths = array(
                FRAMEWORK_PATH,
                EXTENSION_PATH,
                APPLICATION_PATH,
                TEMPLATE_PATH
            );
            
            foreach($paths as $path){
                if (in_array($bundle_name, scandir($path))){
                    if (file_exists($path . $bundle_name . "/bundle.xml")){
                        /* Read the descriptor */
                        $descriptor = file_get_contents($path . $bundle_name . "/bundle.xml");
                        if (xml::is_xml($descriptor)){
                            $this->_path = $path . $bundle_name . "/";
                            
                            $descriptor = xml::parse($descriptor);
                            
                            if ($descriptor instanceof xml){
                                /* Process descriptor */
                                $items = $descriptor->find('bundle')->get(0);
                                
                                for($i = 0; $i < $items->count(); $i++){
                                    $child = $items->get($i);
                                    
                                    if ($child instanceof xml){
                                        $tag = strtolower($child->tag);
                                        
                                        switch($tag){
                                        case "label":
                                        case "name":
                                        case "version":
                                        case "type":
                                        case "namespace":
                                        case "description":
                                        case "copyright":
                                        case "license":
                                        case "schema_installed":
                                            $this->_descriptor[$tag] = $child->get(0);
                                            break;
                                        case "settings":
                                            $this->_descriptor[$tag] = array();
                                            $categories = $child->get();
                                            foreach($categories as $category){
                                                if ($category instanceof xml){
                                                    $name = $category->attr('name');
                                                    
                                                    $settings = array();
                                                    
                                                    if ($name && strlen($name) > 0){
                                                        
                                                        $sets = $category->get();
                                                        foreach($sets as $set){
                                                            if ($set instanceof xml){
                                                                $parts = $set->get();
                                                                
                                                                $setting = array();
                                                                
                                                                foreach($parts as $part){
                                                                    if ($part instanceof xml){
                                                                        $set_tag = strtolower($part->tag);
                                                                        
                                                                        
                                                                        switch ($set_tag){
                                                                        case "name":
                                                                        case "label":
                                                                        case "default_value":
                                                                            $setting[$set_tag] = $part->get(0);
                                                                            break;
                                                                        case "default_values":
                                                                        case "allowed_values":
                                                                            $va = array();
                                                                            $values = $part->get();
                                                                            foreach($values as $value){
                                                                                if ($value instanceof xml && $value->tag == 'value'){
                                                                                    $va[] = $value->get(0);
                                                                                }
                                                                            }
                                                                            $setting[$set_tag] = $va;
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                $settings[] = $setting;
                                                            }
                                                        }
                                                        
                                                        $this->_descriptor[$tag][$name] = $settings;
                                                    }
                                                }
                                            }
                                            break;
                                        
                                        case "depends_on":
                                            $this->_descriptor[$tag] = array();
                                            $nodes = $child->get();
                                            foreach($nodes as $node){
                                                if ($node instanceof xml && strtolower($node->tag) == 'bundle'){
                                                    $this->_descriptor[$tag][] = $node->get(0);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }
                                return true;
                            }
                        }                        
                    }
                }
            }
            
            /* Load failed :/ */
            $this->error("Failed to locate the bundle descriptor file (bundle.xml) for bundle '{$bundle_name}'.");
            return false;
        }
        
        public function apply_settings(){
            if (isset($this->_descriptor['settings'])){
                $settings = $this->_descriptor['settings'];
                foreach($settings as $cat_name => $cat_settings){
                    foreach($cat_settings as $cat_setting){
                        if (isset($cat_setting['name'])){
                            if (isset($cat_setting['default_value'])){
                                $this->setting($cat_setting['name'], $cat_setting['default_value']);
                            }elseif(isset($cat_setting['default_values'])){
                                $this->setting($cat_setting['name'], $cat_setting['default_values']);
                            }
                        }
                    }
                }
            }
        }
        
        public function boot(){
            if (!$this->booted){
                /*
                 * We need to always return true if the bundle is adapt
                 * because we are already within the bootstrap process
                 * for adapt.
                */
                if ($this->name == 'adapt'){
                    /* We need if we have a data_source and if adapt's schema has been created */
                    if ($this->data_source instanceof data_source){
                        if (!isset($this->_descriptor['schema_installed']) || (strtolower($this->_descriptor['schema_installed']) != 'yes')){
                            /* The schema hasn't yet been installed so we can do that now */
                            $this->install();
                        }
                    }
                    return true;
                }else{
                    /* We need if we have a data_source and if adapt's schema has been created */
                    if ($this->data_source instanceof data_source){
                        if (!isset($this->_descriptor['schema_installed']) || (strtolower($this->_descriptor['schema_installed']) != 'yes')){
                            /* The schema hasn't yet been installed so we can do that now */
                            $this->install();
                        }
                    }
                    /*
                     * If this bundle has a boot file, call it
                     */
                    if (file_exists($this->bundle_path . "boot.php")){
                        require_once($this->bundle_path . "boot.php");
                    }
                }
            }
            return true;
        }
        
        
        public function install(){
            if ($this->is_loaded){
                if (!isset($this->_descriptor['schema_installed']) || (strtolower($this->_descriptor['schema_installed']) != 'yes')){
                    $this->store('adapt.installing_bundle', $this->name);
                    
                    if (file_exists($this->bundle_path . "install.php")){
                        require_once($this->bundle_path . "install.php");
                    }
                    
                    $this->store('adapt.installing_bundle', '');
                    $this->_descriptor['schema_installed'] = 'Yes';
                    
                    /* Save the bundle */
                    $this->save();
                }
            }
        }
        
        public function save(){
            $xml = new xml_bundle();
            foreach($this->_descriptor as $key => $value){
                if ($key == 'settings'){
                    $node = new xml_settings();
                    foreach($value as $cat => $sets){
                        $cat_node = new xml_category(array('name' => $cat));
                        $node->add($cat_node);
                        foreach($sets as $set){
                            $set_node = new xml_setting();
                            if (isset($set['name'])){
                                $set_node->add(new xml_name($set['name']));
                            }
                            if (isset($set['label'])){
                                $set_node->add(new xml_label($set['label']));
                            }
                            if (isset($set['default_value'])){
                                $set_node->add(new xml_default_value($set['default_value']));
                            }
                            if (isset($set['default_values']) && is_array($set['default_values'])){
                                $default_values = new xml_default_values();
                                foreach($set['default_values'] as $df){
                                    $default_values->add(new xml_value($df));
                                }
                                $set_node->add($default_values);
                            }
                            if (isset($set['allowed_values']) && is_array($set['allowed_values'])){
                                $allowed_values = new xml_allowed_values();
                                foreach($set['allowed_values'] as $af){
                                    $allowed_values->add(new xml_value($af));
                                }
                                $set_node->add($allowed_values);
                            }
                            $cat_node->add($set_node);
                        }
                    }
                    $xml->add($node);
                }else{
                    $xml->add(new xml($key, $value));
                }
            }
            
            /* Write the bundle back to disc */
            $fp = fopen($this->bundle_path . "bundle.xml", "w");
            if ($fp){
                fwrite($fp, new xml_document('adapt_framework', $xml));
                fclose($fp);
            }
        }
        
    }
    
}

?>