<?php

namespace frameworks\adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class bundles extends base{
        
        public function __construct(){
            parent::__construct();
            
            $bundles = $this->store('adapt.bundles');
            if (!is_array($bundles)){
                $this->store('adapt.bundles', array());
            }
        }
        
        public function get_loaded_bundles(){
            return $this->store('adapt.bundles');
        }
        
        public function cache_bundle($key, $bundle){
            $bundles = $this->get_loaded_bundles();
            $bundles[$key] = $bundle;
            $this->store('adapt.bundles', $bundles);
        }
        
        public function get_bundle($name){
            $bundles = $this->get_loaded_bundles();
            
            if (is_array($bundles) && in_array($name, array_keys($bundles))){
                return $bundles[$name];
            }else{
                $bundle = new bundle($name);
                if ($bundle->is_loaded){
                    $this->cache_bundle($name, $bundle);
                    return $bundle;
                }
            }
            
            return false;
        }
        
        public function download_bundle($bundle_name){
            //TODO: Check settings for a repository username/password or key and then auto login
        }
        
        public function bundle($bundle_name){
            //Creates a .bundle file from a bundle directory
        }
        
        public function unbundle($bundle_file){
            //Unbundles a .bundle file to the correct location
        }
        
        public function list_templates(){
            $templates = array();
            
            if ($dh = opendir(TEMPLATE_PATH)){
                
                while (false !== ($name = readdir($dh))){
                    if (!in_array($name, array('.', '..')) && is_dir(TEMPLATE_PATH . $name)){
                        $templates[] = $name;
                    }
                }
                
            }
            
            return $templates;
        }
        
        public function list_bundles($directory){
            $files = scandir($directory);
            $files = array_remove($files, 0);
            $files = array_remove($files, 0);
            
            return $files;
        }
        
        public function boot_bundles($type){
            $bundles = array();
            
            switch($type){
            case "frameworks":
                $bundles = $this->list_bundles(FRAMEWORK_PATH);
                break;
            case "extensions":
                $bundles = $this->list_bundles(EXTENSION_PATH);
                break;
            case "templates":
                $bundles = $this->list_bundles(TEMPLATE_PATH);
                break;
            case "applications":
                $bundles = $this->list_bundles(APPLICATION_PATH);
                break;
            }
            
            foreach($bundles as $bundle_name){
                $bundle = $this->get_bundle($bundle_name);
                if ($bundle_name != 'adapt' && !is_null($bundle) && $bundle->installed == 'Yes'){
                    if ($bundle->booted == 'No'){
                        /* Check dependencies are booted */
                        $bundle->boot();
                        
                        
                    }
                }
            }
        }
        
        public function boot_frameworks(){
            return $this->boot_bundles('frameworks');
        }
        
        public function boot_extensions(){
            return $this->boot_bundles('extensions');
        }
        
        public function boot_templates(){
            return $this->boot_bundles('templates');
        }
        
        public function boot_application($bundle_name = null){
            /*
             * Boot order:
             * 1. The bundle provided in the param
             * 2. The bundle named in the setting
             *    adapt.default_application
             * 3. The first bundle in the application
             *    folder
             */
            
            if (is_null($bundle_name)){
                $bundle_name = $this->setting('adapt.default_application');
                
                if (is_null($bundle_name)){
                    $applications = $this->list_bundles(APPLICATION_PATH);
                    $bundle_name = array_pop($applications);
                }
            }
            
            if (isset($bundle_name)){
                
                $bundle = $this->get_bundle($bundle_name);
                if ($bundle instanceof bundle && $bundle->is_loaded){
                    
                    $this->store('adapt.running_application', $bundle_name);
                    if ($bundle->boot()){
                        return true;
                    }
                    
                    $this->error('Failed to boot application: ' . $bundle_name);
                    $this->store('adapt.running_application', '');
                }else{
                    $this->error('Failed to load application: ' . $bundle_name);
                }
                
            }else{
                $this->error('No applications found');
            }
            
            return false;
        }
        
    }
    
}

?>