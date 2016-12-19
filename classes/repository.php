<?php

namespace adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class repository extends base{
        
        protected $_url;
        protected $_session_token;
        protected $_http;
        
        public function __construct($url, $username = null, $password = null){
            parent::__construct();
            
            $this->_http = new http();
            $this->_url = $url;
            if (isset($username) && isset($password)){
                $this->login($username, $password);
            }
        }
        
        protected function _request($uri){
            $response = $this->_http->get($this->_url . $uri);
            
            if ($response['status'] == 200){
                return $content;
            }
            
            return false;
        }
        
        protected function _post($uri, $data, $content_type = "application/json", $headers = []){
            $response = $this->_http->post($this->_url . $uri, $data, array_merage($headers, ['content-type' => $content_type]));
            
            if ($response['status'] == 200){    
                return $content;
            }
            
            return false;
        }
        
        public function login($username, $password){
            $uri = "/login";
            $data =  [
                'email_address' => $username,
                'password' => $password
            ];
            
            $response = $this->_post($uri, $data);
            
            if (is_json($response)){
                $response = json_decode($response, true);
                if (is_array($response)){
                    $keys = array_keys($response);
                    if (in_array('success', $keys)){
                        $this->_session_token = $response['success']['token'];
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        public function post($bundle_file){
            if (file_exists($bundle_file)){
                $content = file_get_contents($bundle_file);
                
                if ($content){
                    $uri = "/bundles/upload";
                    $response = $this->_post($uri, $content, "application/x-bundle", ['token' => $this->_session_token]);
                    
                    if (is_array($response)){
                        $keys = array_keys($response);
                        
                        if (in_array('success', $keys)){
                            return true;
                        }elseif (in_array('error', $keys)){
                            switch($response['error']['code']){
                            case "unable_to_load":
                            case "unable_to_store":
                                $this->error("Repository error: {$response['error']['message']}");
                                break;
                            case "bundle_already_loaded":
                                $this->error("The repository cannot process your request at present.");
                                break;
                            case "unable_to_process_bundle":
                                $this->error("Repository error: {$response['error']['message']}");
                                $this->error($response['error']['error']);
                                break;
                            }
                        }
                    }
                }else{
                    $this->error("Unable to read file or file is empty");
                }
            }else{
                $this->error('File not found');
            }
            
            return false;
        }
        
        public function get_dependency_list($bundle_name, $bundle_version){
            if (preg_match("/^[a-zA-Z]+[-_a-zA-Z0-9]+[a-zA-Z0-9]+$/", $bundle_name)){
                
                $uri = "/bundles/{$bundle_name}";
                if ($bundle_version){
                    if (preg_match("/^[0-9]+(\.[0-9]+){0,2}$/", $bundle_version)){
                        $uri .= "/{$bundle_version}";
                    }else{
                        $this->error("Invalid bundle version '{$bundle_version}'");
                        return false;
                    }
                }else{
                    $uri .= "/latest";
                }
                
                $response = $this->_request($uri);
                
                if ($response !== false){
                    $output = [];
                    
                    $depends_on = $response->find('depends_on bundle')->get();
                    foreach($depends_on as $dependency){
                        $output[] = [
                            'name' => $dependency->find('name')->text(),
                            'version' => $dependency->find('version')->text()
                        ];
                    }
                    
                    return $output;
                }else{
                    $this->error("Bundle not found");
                    return false;
                }
                
                
            }else{
                $this->error("Invalid bundle name '{$bundle_name}'");
                return false;
            }
            
            return false;
        }
        
        /**
         * @todo
         */
        //public function get_works_with_list($bundle_name, $bundle_versions = array()){
        //    
        //}
        
        public function has($bundle_name, $bundle_version = null){
            if (preg_match("/^[a-zA-Z]+[-_a-zA-Z0-9]+[a-zA-Z0-9]+$/", $bundle_name)){
                
                $uri = "/bundles/{$bundle_name}";
                if ($bundle_version){
                    if (preg_match("/^[0-9]+(\.[0-9]+){0,2}$/", $bundle_version)){
                        $uri .= "/{$bundle_version}";
                    }else{
                        $this->error("Invalid bundle version '{$bundle_version}'");
                        return false;
                    }
                }else{
                    $uri .= "/latest";
                }
                
                $response = $this->_request($uri);
                if($response instanceof xml){
                    return $response->find('bundles > bundle > version')->get(0)->get(0);
                }
                
            }else{
                $this->error("Invalid bundle name '{$bundle_name}'");
                return false;
            }
            
            return false;
        }
        
        public function get_bundle_types(){
            $bundle_types = $this->cache->get("adapt/repository/bundle_types");
            
            if (!is_array($bundle_types)){
                $bundle_types = [];
                $uri = "/bundle-types";
                
                $data = $this->_request($uri);
                
                if ($data instanceof xml){
                    $type_nodes = $data->find('bundle_type')->get();
                    foreach($type_nodes as $node){
                        $bundle_type = [];
                        
                        $children = $node->get();
                        foreach($children as $child){
                            if ($child instanceof xml){
                                switch($child->tag){
                                case "name":
                                case "label":
                                case "description":
                                    $bundle_type[$child->tag] = $child->get(0);
                                    break;
                                }
                            }
                        }
                        
                        if (isset($bundle_type['name']) && isset($bundle_type['label'])){
                            $bundle_types[] = $bundle_type;
                        }
                    }
                    
                    if (count($bundle_types)){
                        /* Cache the result for the future */
                        $this->cache->serialize("adapt/repository/bundle_types", $bundle_types, 60 * 60 * 24 * 7);
                    }
                }
            }
            
            return $bundle_types;
        }
        
        public function get($bundle_name, $bundle_version = null){
            if ($version = $this->has($bundle_name, $bundle_version)){
                /* Download the bundle */
                $uri = $this->_url . "/bundles/{$bundle_name}/{$version}/download";
                
                /* We need to use the HTTP object because this is non-standard output */
                $response = $this->_http->get($uri);
                
                if (is_array($response) && $response['status'] == 200){
                    /* Store the bundle */
                    $key = "adapt/repository/{$bundle_name}-{$version}.bundle";
                    $this->file_store->set($key, $response['content'], "application/octet-stream");
                    
                    $path = $this->file_store->get_file_path($key);
                    
                    if ($path !== false){
                        /* Lets unbundle the bundle */
                        $this->unbundle($path);
                    }else{
                        $this->error("Unable to store bundle '{$bundle_name}' version '{$version}'");
                    }
                    
                }else{
                    $this->error("Failed to download bundle '{$bundle_name}' version '{$version}'");
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
                        $version = $manifest->find('version');
                        $name = trim($name->get(0)->text);
                        $type = trim($type->get(0)->text);
                        $version = trim($version->get(0)->text);

                        /* Is this bundle already installed? */
                        if ($this->bundles->has_bundle($name, $version) === false){
                            /*
                             * The bundle isn't installed so we are going
                             * to unbundle it
                             */
                            $path = ADAPT_PATH;
                            
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
        
        public function bundle($bundle_name, $bundle_version){
            
        }
    }
}

?>
