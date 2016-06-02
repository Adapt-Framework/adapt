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

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class cache extends base{
        
        protected $_file_store;
        
        public function __construct(){
            parent::__construct();
        }
        
        
        public function pget_file_store(){
            $parent_store = parent::pget_file_store();
            if ($parent_store instanceof storage_file_system){
                return $parent_store;
            }elseif(isset($this->_file_store)){
                return $this->_file_store;
            }else{
                $this->_file_store = new storage_file_system();
                return $this->_file_store;
            }
        }
        
        
        public function get($key){
            if (strtolower($this->setting('adapt.disable_caching')) != 'yes'){
                $key = $this->_get_key($key);
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
            }
            return null;
        }
        
        public function get_sql($key){
            return $this->get("adapt/sql/" . md5($sql));
        }
        
        public function get_content_type($key){
            $key = $this->_get_key($key);
            return $this->file_store->get_content_type($key);
        }
        
        
        
        public function set($key, $data, $expires = 300, $content_type = null, $public = false){
            if (strtolower($this->setting('adapt.disable_caching')) != 'yes'){
                //print "<pre>{$key} becomes ";
                $key = $this->_get_key($key);
                //print "{$key}</pre>";
                $this->file_store->set($key, $data, $content_type, $public);
                $date = new date();
                $date->goto_seconds($expires);
                $this->file_store->set_meta_data($key, 'expires', $date->date('Y-m-d H:i:s'));
            }
        }
        
        public function delete($key){
            $key = $this->_get_key($key);
            $this->file_store->delete($key);
        }
        
        public function sql($key, $sql_results = array(), $expires = null){
            $key = "adapt/sql/" . md5($key);
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
        
        protected function _get_key($key){
            return "adapt/cache/" . $key;
        }
        
    }
    
}

?>