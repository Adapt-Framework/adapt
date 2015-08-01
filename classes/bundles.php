<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2015 Adapt Framework (www.adaptframework.com)
 * Authored by Matt Bruton (matt@adaptframework.com)
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
 */

namespace frameworks\adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class bundles extends base{
        
        protected $_settings;
        
        public function __construct(){
            parent::__construct();
            
            $this->_settings = array();
            
            $bundles = $this->store('adapt.bundles');
            if (!is_array($bundles)){
                $this->store('adapt.bundles', array());
            }
        }
        
        public function has($bundle_name){
            $paths = array(
                FRAMEWORK_PATH,
                EXTENSION_PATH,
                APPLICATION_PATH,
                TEMPLATE_PATH
            );
            
            foreach($paths as $path){
                if (in_array($bundle_name, scandir($path))){
                    return true;
                }
            }
            return false;
        }
        
        public function get($bundle_name){
            if ($this->has($bundle_name)){
                
                /*
                 * Is the bundle cached?
                 */
                $cached = $this->store('adapt.bundles');
                foreach($cached as $name => $bd){
                    if ($name == $bundle_name){
                        return $bd;
                    }
                }
                
                /*
                 * Lets load it
                 */
                
                $bundle = new bundle($bundle_name);
                if (count($bundle->errors())){
                    $errors = $bundle->errors(true);
                    foreach($errors as $error){
                        $this->error("Bundle {$bundle_name}: {$error}");
                    }
                }else{
                    $this->cache($bundle_name, $bundle);
                    return $bundle;
                }
            }else{
                /* We couldn't find the bundle locally
                 * so we are going to search the respository
                 * and see if we can get us a copy.
                 */
                $results = $this->search_repository($bundle_name);
                
                if (count($results) >= 1){
                    /*
                     * Lets see if it's in the results
                     */
                    foreach($results as $result){
                        
                        if ($result['name'] == $bundle_name){
                            /* We've found it, so lets download it */
                            $this->download($bundle_name);
                            if ($this->has($bundle_name)){
                                $bundle = new bundle($bundle_name);
                                if (count($bundle->errors())){
                                    $errors = $bundle->errors(true);
                                    foreach($errors as $error){
                                        $this->error("Bundle {$bundle_name}: {$error}");
                                    }
                                }else{
                                    return $bundle;
                                }
                            }
                        }
                    }
                    
                }
                
                $this->error("Unable to locate bundle {$bundle_name}");
            }
        }
        
        public function cache($key, $bundle){
            $bundles = $this->store('adapt.bundles');
            $bundles[$key] = $bundle;
            $this->store('adapt.bundles', $bundles);
        }
        
        public function search_repository($q){
            /*
             * TEMP CODE
             * This code is here to simulate the
             * respoitory, once the repository is
             * built this code must be replaced
             * with the proper code.
             */
            
            return array(array(
                'name' => $q,
                'description' => '...',
                'version' => '...',
                '...' => '...'
             ));
        }
        
        public function download($bundle_name){
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
                        $name = trim($name->get(0)->text);
                        $type = trim($type->get(0)->text);
                        
                        if (in_array(strtolower($type), array('application', 'extension', 'framework', 'template'))){
                            /* Is this bundle already installed? */
                            if ($this->has($name) === false){
                                /*
                                 * The bundle isn't installed so we are going
                                 * to unbundle it
                                 */
                                $path = '';
                                switch($type){
                                case 'application':
                                    $path = APPLICATION_PATH;
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
        
        public function bundle($bundle_path){
            
        }
        
        public function install($bundle_name){
            
        }
        
        public function boot($bundle_name = null){
            if (is_null($bundle_name)){
                //Boots an application
                if (count($this->_settings) == 0) $this->load_settings();
                
                
                /* If we have a data_source lets connect it */
                if (isset($this->_settings['datasource.driver']) && isset($this->_settings['datasource.host']) && isset($this->_settings['datasource.username'])){
                    
                    /* So we have found a data_source, lets connect it */
                    $drivers = $this->_settings['datasource.driver'];
                    $hosts = $this->_settings['datasource.host'];
                    $usernames = $this->_settings['datasource.username'];
                    $passwords = $this->_settings['datasource.password'];
                    $schemas = $this->_settings['datasource.schema'];
                    $writables = $this->_settings['datasource.writable'];
                    
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
                    
                    
                }
                
                /* Find the application to boot */
                $bundle_name = null;
                
                if (isset($this->_settings['adapt.default_application'])){
                    /* We have a default app */
                    $bundle_name = $this->_settings['adapt.default_application'];
                }else{
                    /* We don't have a default app, so lets boot the first we find */
                    $applications = scandir(APPLICATION_PATH);
                    $applications = array_remove($applications, 0);
                    $applications = array_remove($applications, 0);
                    
                    if (count($applications)){
                        $bundle_name = $applications[0];
                    }
                }
                
                $this->setting('adapt.running_application', $bundle_name);
            }
            
            if (!is_null($bundle_name)){
                $bundle = $this->get($bundle_name);
                
                if ($bundle && $bundle instanceof bundle && $bundle->is_loaded){
                    
                    if ($bundle->booted == false){
                        /* Before we boot this bundle we need to ensure
                         * that all dependencies are booted first
                         */
                        $dependencies_resolved = true;
                        
                        foreach($bundle->depends_on as $dependent){
                            if (!$this->boot($dependent)){
                                $this->error("Unable to boot '{$bundle_name}' because it depends on '{$dependent}' which is unavailable");
                                $dependencies_resolved = false;
                            }
                        }
                        
                        if ($dependencies_resolved){
                            /*
                             * This bundle has everything it needs to boot but
                             * before we do so we need to import the bundles
                             * settings followed by importing global settings.
                             */
                            $bundle->apply_settings();
                            $this->apply_global_settings();
                            
                            return $bundle->boot();
                        }
                    }else{
                        return true;
                    }
                    
                }else{
                    $this->error("Unable to boot application '{$bundle_name}'");
                }
            }else{
                $this->error('Unable to find a valid application to boot.');
            }
        }
        
        public function apply_global_settings(){
            foreach($this->_settings as $name => $value){
                if ($name && strlen($name) > 0){
                    $this->setting($name, $value);
                }
            }
        }
        
        public function load_settings(){
            /*
             * We are going to load the global
             * settings and apply them to each bundle
             * before booting.
             */
            if (count($this->_settings) == 0){
                /* Do we have a cached copy? */
                $cached_settings = parent::aget_cache()->get('adapt.settings');
                if ($cached_settings && is_array($cached_settings)){
                    $this->_settings = $cached_settings;
                }else{
                    /* Only load them if they are not already loaded */
                    if (file_exists(ADAPT_PATH . "settings.xml")){
                        $settings = trim(file_get_contents(ADAPT_PATH . "settings.xml"));
                        
                        if ($settings && strlen($settings) > 0 && xml::is_xml($settings)){
                            
                            $settings = xml::parse($settings);
                            if ($settings instanceof xml){
                                $settings = $settings->find('settings')->get(0);
                                
                                if ($settings instanceof xml){
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
                                                $this->_settings[$name] = $value;
                                            }
                                        }
                                    }
                                }
                                
                                /* Cache the settings */
                                parent::aget_cache()->serialize('adapt.settings', $this->_settings, 120);
                            }
                        }
                    }
                }
            }
        }
    }
    
}

?>