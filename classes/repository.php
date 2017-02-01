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
                return $response['content'];
            }
            
            return false;
        }
        
        protected function _post($uri, $data, $content_type = "application/json", $headers = []){
            $response = $this->_http->post($this->_url . $uri, $data, array_merge($headers, ['content-type' => $content_type]));
            if ($response['status'] == 200){    
                return $response['content'];
            }
            
            return false;
        }
        
        public function login($username, $password){
            $uri = "/login";
            $data =  [
                'email_address' => $username,
                'password' => $password
            ];
            
            $response = $this->_post($uri, json_encode($data));
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
                
                $uri = "/bundles/bundle/{$bundle_name}";
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
                if (is_json($response)){
                    $response = json_decode($response, true);
                    return $response['repository_bundle_version']['version'];
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
                $uri = $this->_url . "/bundles/bundle/{$bundle_name}/{$version}/download";
                
                /* We need to use the HTTP object because this is non-standard output */
                $response = $this->_http->get($uri);

                if (is_array($response) && $response['status'] == 200){
                    $temp_file = TEMP_PATH . guid();
                    file_put_contents($temp_file, $response['content']);
                    /* Store the bundle */
                    $key = "adapt/repository/{$bundle_name}-{$version}.bundle";
//                    print_r($response);
                    //$this->file_store->set($key, $response['content'], "application/octet-stream");
                    $this->file_store->set_by_file($key, $temp_file, "application/octet-stream");
                    print "Bundle file key '{$key}'\n";
                    print "Temp file: {$temp_file}\n";
                    unlink($temp_file);
                    $unbundler = new unbundler();
                    if ($unbundler->load($key)){
                        $bundle_xml = xml::parse($unbundler->extract_file("bundle.xml"));
                        if ($bundle_xml instanceof xml){
                            $name = $bundle_xml->find('bundle > name')->first()->text();
                            $version = $bundle_xml->find('bundle > version')->first()->text();
                            $path = ADAPT_PATH . $name . "/" . $name . "-" . $version;
                            $unbundler->make_dir($path);
                            $unbundler->extract_all($path);
                            
                            return $name;
                        }else{
                            $this->error("Unable to read bundle.xml for {$bundle_name} v{$bundle_version}");
                            return false;
                        }
                    }else{
                        $this->error("Unable to process bundle.");
                        $this->error($unbundler->errors(true));
                        return false;
                    }
                    
                }else{
                    $this->error("Failed to download bundle '{$bundle_name}' version '{$version}'");
                }
            }
            
            return false;
        }
        
        public function check_for_updates(){
            $checked = [];
            if ($this->data_source && $this->data_source instanceof data_source_sql){
                $sql = $this->data_source->sql;
                
                $sql->select('name', 'version', 'type')->from('bundle_version')->where(new sql_cond('date_deleted', sql::IS, new sql_null()));
                
                $results = $sql->execute()->results();
                
                foreach($results as $result){
                    list($major, $minor, $revision) = explode(".", $result['version']);
                    $version = "{$major}.{$minor}";
                    $array_key = "{$result['name']}-{$version}";
                    if (!in_array($array_key, $checked)){
                        $latest_revision = $this->has($result['name'], $version);
                        if (bundles::matches_version($result['version'], $latest_revision)){
                            if (bundles::get_newest_version($result['version'], $latest_revision) == $latest_revision){
                                // Add the bundle to the bundle version table
                                $model = new model_bundle_version();
                                $model->name = $revision['name'];
                                $model->version = $latest_revision;
                                $model->type = $result['type'];
                                $model->local = 'No';
                                $model->installed = 'No';
                                if ($model->save()){
                                    $checked[] = $array_key;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
