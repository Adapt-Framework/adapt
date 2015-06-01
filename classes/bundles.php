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
        
        public function get_bundle($name, $download = true){
            $bundles = $this->get_loaded_bundles();
            
            if (is_array($bundles) && in_array($name, array_keys($bundles))){
                return $bundles[$name];
            }else{
                $bundle = new bundle($name);
                if ($bundle->is_loaded){
                    $this->cache_bundle($name, $bundle);
                    return $bundle;
                }elseif($download == true){
                    /*
                     * Lets attempt to download the bundle
                     * from the repository
                     */
                    if ($this->download_bundle($name)){
                        /* This bundle should now be available */
                        $bundle = new bundle($name);
                        if ($bundle->is_loaded){
                            /* Ok we sucessfully downloaded it, lets install it! */
                            $bundle->install();
                            $this->cache_bundle($name, $bundle);
                            return $bundle;
                        }
                    }
                    
                }
            }
            
            return false;
        }
        
        public function download_bundle($bundle_name){
            $repos = $this->setting('repository.url');
            $users = $this->setting('repository.username');
            $passwords = $this->setting('repository.password');
            
            if (is_array($repos) && count($repos) && is_array($users) && is_array($passwords) && count($repos) == count($users) && count($users) == count($passwords)){
                for($i = 0; $i < count($repos); $i++){
                    $url = $repos[$i];
                    $username = $users[$i];
                    $password = $passwords[$i];
                    
                    /*
                     * This code is to access the temp repo until the
                     * real repo is build. Had to do it, couldn't continue
                     * building the framework without repo support, couldn't
                     * build the repo with out the framework :/ What can you do?
                     */
                    //$http = new http();
                    //$response = $http->get($url . "/adapt/bundles/{$bundle_name}.bundle");
                    $response = array(
                        'status' => 200,
                        'content' => file_get_contents($url . "/adapt/bundles/{$bundle_name}.bundle")
                    );
                    
                    if ($response['status'] == 200){
                        /*
                         * Ok we have a bundle so we need to write it
                         * to the temp directory
                         */
                        $temp_name = TEMP_PATH . 'adapt' . md5(rand(0, 999999)) . '.bundle';
                        //print "Writing bundle to: {$temp_name}";
                        $fp = fopen($temp_name, "w");
                        if ($fp){
                            fwrite($fp, $response['content']);
                            fclose($fp);
                            //exit(1);
                            /* Lets unbundle the file */
                            $output = $this->unbundle($temp_name);
                            
                            unlink($temp_name);
                            
                            return $output;
                        }else{
                            //print "Failed to write to temp";
                            $this->error('Unable to write to temp directory: ' . TEMP_PATH);
                        }
                        break;
                    }else{
                        $this->error("Received {$response['status']} from the repository.");
                    }
                    
                }
            }
            
            return false;
        }
        
        public function bundle($bundle_name){
            //Creates a .bundle file from a bundle directory
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
                        if ($file['name'] == 'manifest.xml'){
                            fseek($fp, $offset);
                            $manifest = fread($fp, $file['length']);
                        }
                        $offset += $file['length'];
                    }
                    
                    if (xml::is_xml($manifest)){
                        $manifest = xml::parse($manifest);
                        $name = $manifest->find('name');
                        $type = $manifest->find('type');
                        $name = trim($name->get(0)->text);
                        $type = trim($type->get(0)->text);
                        
                        if (in_array(strtolower($type), array('application', 'extension', 'framework', 'template'))){
                            /* Is this bundle already installed? */
                            if ($this->get_bundle($name, false) === false){
                                /*
                                 * The bundle isn't installed so we are going
                                 * to unbundle it
                                 */
                                $path = '';
                                switch($type){
                                case 'application':
                                    $path = FRAMEWORKS_PATH;
                                    break;
                                case 'template':
                                    $path = TEMPLATE_PATH;
                                    break;
                                case 'extension':
                                    $path = EXTENSION_PATH;
                                    break;
                                case 'framework':
                                    $path = FRAMEWORK_PATH;
                                    break;
                                }
                                
                                mkdir($path . $name);
                                $path .= $name . '/';
                                
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
                                return true;
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
            /*
             * This function is no longer required
             * as we now boot on demand
             */
            return $this->boot_bundles('frameworks');
        }
        
        public function boot_extensions(){
            /*
             * This function is no longer required
             * as we now boot on demand
             */
            return $this->boot_bundles('extensions');
        }
        
        public function boot_templates(){
            /*
             * This function *may* no longer be required
             * as we now boot on demand
             */
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