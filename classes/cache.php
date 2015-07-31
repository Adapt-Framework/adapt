<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class cache extends base{
        
        public function __construct(){
            parent::__construct();
        }
        
        public function get($key){
            $key = md5($key);
            $data = $this->file_store->get($key);
            if (!is_null($data)){
                $expires = $this->file_store->get_meta_data($key, 'expires');
                if (!is_null($expires)){
                    $date = new date($expires);
                    if ($date->is_past(true)){
                        $this->file_store->delete($key);
                        return null;
                    }else if ($this->file_store->get_content_type($key) == 'application/octet-stream'){
                        $data = unserialize($data);
                    }
                    
                    return $data;
                }
            }
            return null;
        }
        
        public function get_content_type($key){
            $key = md5($key);
            return $this->file_store->get_content_type($key);
        }
        
        public function set($key, $data, $expires = 300, $content_type = null, $public = false){
            $key = md5($key);
            $this->file_store->set($key, $data, $content_type, $public);
            $date = new date();
            $date->goto_seconds($expires);
            $this->file_store->set_meta_data($key, 'expires', $date->date('Y-m-d H:i:s'));
        }
        
        public function delete($key){
            $this->file_store->delete($key);
        }
        
        public function sql($key, $sql_results = array(), $expires = null){
            if (is_null($expires)){
                $expires = $this->setting('adapt.sql_cache_expires_after');
            }
            
            $this->set($key, serialize($sql_results), $expires, 'application/octet-stream');
        }
        
        public function view($key, $view, $expires = null){
            if (is_null($expires)){
                $expires = $this->setting('adapt.view_cache_expires_after');
            }
            
            $this->set($key, serialize($view), $expires, 'application/octet-stream');
        }
        
        public function page($key, $html, $expires = null){
            if (is_null($expires)){
                $expires = $this->setting('adapt.page_cache_expires_after');
            }
            
            $this->set($key, $html, $expires, 'text/html');
        }
        
        public function serialize($key, $object, $expires = null){
            $this->set($key, serialize($object), $expires, 'application/octet-stream');
        }
        
        public function javascript($key, $javascript, $expires = 600, $public = false){
            $this->set($key, $javascript, $expires, 'text/javascript', $public);
        }
        
        public function css($key, $css, $expires = 600, $public = false){
            $this->set($key, $css, $expires, 'text/css', $public);
        }
        
    }
    
}

?>