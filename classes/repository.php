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
            $response =  $this->_http->get($this->_url . $uri);
            
            if ($response['status'] == 200 && $response['headers']['content-type'] == "application/xml"){
                $content = xml::parse($response['content']);
                if ($content instanceof xml){
                    if (!isset($this->_session_token)){
                        $this->_session_token = $content->find('repository')->attr('session-key');
                    }
                    
                    return $content;
                }
            }
            
            return false;
        }
        
        protected function _post($uri, $data, $content_type = "application/xml"){
            $response = $this->_http->post($this->_url . $uri, $data, ['content-type' => $content_type]);
            //print_r($response);
            if ($response['status'] == 200 && $response['headers']['content-type'] == "application/xml"){
                $content = xml::parse($response['content']);
                if ($content instanceof xml){
                    if (!isset($this->_session_token)){
                        $this->_session_token = $content->find('repository')->attr('session-key');
                        
                    }
                    
                    return $content;
                }
            }
            
            return false;
        }
        
        public function login($username, $password){
            $uri = "?actions=api/login";
            $data = new xml_action(
                [
                    new xml_username($username),
                    new xml_password($password)
                ],
                ['name' => 'api/login']
            );
            
            $response = $this->_post($uri, new xml_adapt_framework($data));
            
            if ($response !== false){
                if ($response instanceof xml){
                    if ($response->find('[name="api/login"] success')->size() == 1){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
            
            return false;
        }
        
        public function post($bundle_file){
            if (file_exists($bundle_file)){
                $content = file_get_contents($bundle_file);
                
                if ($content){
                    $uri = "?actions=api/bundles/post";
                    
                    $response = $this->_post($uri, $content, "application/octet-stream");
                    print $response;
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
        
        public function get_works_with_list($bundle_name, $bundle_versions = array()){
            
        }
        
        public function has($bundle_name, $bundle_version = null){
            print $bundle_name . "\n";
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
                //print $response->find('bundles > bundle > version')->get(0)->get(0);
                return $response->find('bundles > bundle > version')->get(0)->get(0);
                
                
            }else{
                $this->error("Invalid bundle name '{$bundle_name}'");
                return false;
            }
            
            return false;
            
            $uri = "/bundles/{$bundle_name}";
            
            if ($content = $this->request($uri)){
                return true;
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
                print $uri;
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
            
            /*
             * This code is to access the temp repo until the
             * real repo is build. Had to do it, couldn't continue
             * building the framework without repo support, couldn't
             * build the repo with out the framework :/ What can you do?
             */
            
            $output = "output";
            
            $repo = $this->setting('repository.url');
            $url = $repo[0];
            
            $http = new http();
            $response = $http->get($url . "/bundles/{$bundle_name}/{$bundle_version}");
            $output = print_r($response, true);
            
            return $output;
            
            //$http = new http();
            //$response = $http->get($url . "/adapt/bundles/{$bundle_name}.bundle");
            $response = array(
                'status' => 200,
                /*'content' => file_get_contents("http://repo.adaptframework.com/adapt/bundles/{$bundle_name}.bundle")*/
                'content' => file_get_contents("https://matt:poo@hyperion.matt.wales/files/Projects/adapt_framework/bundled/{$bundle_name}.bundle")
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
                    $output = $this->bundles->unbundle($temp_name);
                    
                    unlink($temp_name);
                    
                    if ($output == false){
                        $this->error("Unable to unbundle '{$bundle_name}' from '{$temp_name}'");
                        $errors = $this->bundles->errors(true);
                        foreach($errors as $error){
                            $this->error($error);
                        }
                    }
                    
                    return $output;
                }else{
                    //print "Failed to write to temp";
                    $this->error('Unable to write to temp directory: ' . TEMP_PATH);
                }
                //break;
            }else{
                $this->error("Received {$response['status']} from the repository.");
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
                        //list($major, $minor, $revision) = explode(".", $version);
                        
                        //if (in_array(strtolower($type), array('application', 'extension', 'framework'))){
                            /* Is this bundle already installed? */
                            if ($this->bundles->has_bundle($name, $version) === false){
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
                        //}
                        
                        
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
        
    }
}

?>
