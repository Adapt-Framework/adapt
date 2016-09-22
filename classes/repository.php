<?php

namespace adapt{
    
    /*
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    class repository extends base{
        
        protected $_url;
        protected $_session_token;
        
        public function __construct($url, $username = null, $password = null){
            parent::__construct();
            
            $this->_url = $url;
            
            if (isset($username) && isset($password)){
                $this->login($username, $password);
            }
        }
        
        public function login($username, $password){
            
        }
        
        public function get_dependency_list($bundle_name, $bundle_versions = array()){
            
        }
        
        public function get_works_with_list($bundle_name, $bundle_versions = array()){
            
        }
        
        public function has($bundle_name, $bundle_versions = array()){
            return true;
        }
        
        public function get($bundle_name, $bundle_version = null){
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
        
    }
}

?>
