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
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class storage_file_system extends base implements interfaces\storage_file{
        
        protected $_suppress_errors;
        protected $_store_path;
        protected $_store_available = false;
        
        public function  __construct(){
            parent::__construct();
            $this->_suppress_errors = false;
            $this->_store_path = $this->setting('adapt.file_store_path');
            
            if (!file_exists($this->_store_path)){
                $dir = dirname($this->_store_path);
                if (!is_writable($dir)){
                    $this->error("{$dir} is not writable");
                }else{
                    mkdir($this->_store_path);
                    $fp = fopen($this->_store_path . ".htaccess", "w");
                    if ($fp){
                        fputs($fp, "deny from all");
                    }
                    fclose($fp);
                }
            }
            
            if (is_writable($this->_store_path)){
                $this->_store_available = true;
            }else{
                $this->error("{$this->_store_path} is not writable");
            }
        }
        
        public function pget_suppress_errors(){
            return $this->_suppress_errors;
        }
        
        public function pset_suppress_errors($value){
            $this->_suppress_errors = $value;
        }
        
        public function pget_available(){
            return $this->_store_available;
        }
        
        public function get_file_path($key){
            if ($this->is_key_valid($key)){
                $path = $this->_store_path . $key;
                if (file_exists($path)){
                    return $path;
                }
                
            }else{
                $this->error('Invalid file key');
            }
            
            return false;
        }
        
        public function error($error){
            if (!$this->_suppress_errors){
                parent::error($error);
            }
        }
        
        public function is_key_valid($key){
            if (!strpos($key, "..")){
                return preg_match("/[0-9a-zA-Z]+(\/?[-._A-Za-z0-9]+)*/", $key);
            }
            
            return false;
        }
        
        public function get_new_key(){
            if ($this->available){
                $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwzyz0123456789";
                $key_size = 10;
                
                $key = date('Ymdhis');
                for($i = 0; $i < $key_size; $i++){
                    $key .= substr($chars, rand(0, strlen($chars) - 1), 1);
                }
                
                return $key;
            }else{
                $this->error("Unable to generate key, storage unavailable");
            }
        }
        
        public function set($key, $data, $content_type = null){
            if ($this->available){
                if ($this->is_key_valid($key)){
                    $path = $this->_store_path;
                    $namespaces = explode("/", $key);

                    if (count($namespaces) > 1){
                        for($i = 0; $i < count($namespaces) - 1; $i++){
                            $path .= $namespaces[$i] . "/";
                            if (!file_exists($path)){
                                mkdir($path);
                            }
                        }
                        
                        $path .= $namespaces[count($namespaces) - 1];
                    }else{
                        $path .= $namespaces[0];
                    }

                    $fp = fopen($path, "w");
                    if ($fp){
                        fwrite($fp, $data);
                        fclose($fp);
                        
                        if (!is_null($content_type)) $this->set_content_type($key, $content_type);
                        
                        return true;
                    }else{
                        $this->error("Unable to write file {$this->_store_path}{$key}");
                    }
                    
                }else{
                    $this->error("The key '{$key}' is not valid.");
                }
            }else{
                $this->error("File system file storage is unavailable.");
            }
            
            return false;
        }
        
        
        public function set_by_file($key, $file_path, $content_type = null, $public = false){
            if ($this->available){
                if ($this->is_key_valid($key)){
                    $path = $this->_store_path;
                    $namespaces = explode("/", $key);
                    
                    if (count($namespaces) > 1){
                        for($i = 0; $i < count($namespaces) - 1; $i++){
                            $path .= $namespaces[$i] . "/";
                            if (!file_exists($path)){
                                mkdir($path);
                            }
                        }
                        
                        $path .= $namespaces[count($namespaces) - 1];
                    }else{
                        $path .= $namespaces[0];
                    }
                    
                    $fp = fopen($path, "w");
                    if ($fp){
                        fwrite($fp, file_get_contents($file_path));
                        fclose($fp);
                        
                        if (!is_null($content_type)) $this->set_content_type($key, $content_type);
                        
                        return true;
                    }else{
                        $this->error("Unable to write file {$this->_store_path}{$key}");
                    }
                    
                }else{
                    $this->error("The key '{$key}' is not valid.");
                }
            }else{
                $this->error("File system file storage is unavailable.");
            }
            
            return false;
        }
        
        public function get($key, $number_of_bytes = null, $offset = 0){
            if ($this->is_key_valid($key)){
                $path = $this->_store_path . $key;
                if (file_exists($path)){
                    if (!is_null($number_of_bytes)){
                        return file_get_contents($path, false, null, $offset, $number_of_bytes);
                    }else{
                        return file_get_contents($path, false, null, $offset);
                    }
                }
            }else{
                $this->error("Invalid file storage key '{$key}'");
            }
            
            return null;
        }
        
        public function write_to_file($key, $path = null){
            if ($this->is_key_valid($key)){
                if (is_null($path)) $path = TEMP_PATH . $this->get_new_key();
                if (file_exists($this->_store_path . $key)){
                    if (is_writable(dirname($path))){
                        $fp = fopen($path, "w");
                        
                        if ($fp){
                            fwrite($fp, $this->get($key));
                            fclose($fp);
                            
                            return $path;
                        }else{
                            $this->error("Unable to write_to_file, could not write to " . $path);
                        }
                    }else{
                        $this->error("Unable to write_to_file, could not write to " . dirname($path));
                    }
                }else{
                    $this->error("Unable to write_to_file, could not find key {$key}");
                }
            }
            
            return false;
        }
        
        public function delete($key){
            $path = $this->_store_path . $key;
            if ($this->available){
                if ($this->is_key_valid($key)){
                    if (file_exists($this->_store_path . $key)){
                        unlink($this->_store_path . $key);
                    }
                    
                    if (file_exists($this->_store_path . $key . ".meta")){
                        unlink($this->_store_path . $key . ".meta");
                    }
                    return true;
                }else{
                    $this->error("Invalid file storage key '{$key}'");
                }
            }else{
                $this->error("Unable to delete the file, storage unavailable");
            }
            
            return false;
        }
        
        public function delete_path($path){
            if (is_dir($this->_store_path . $path)){
                if (substr($path, strlen($path) - 1, 1) != "/"){
                    $path .= "/";
                }
                $files = glob($this->_store_path . $path . "*", GLOB_MARK);
                foreach($files as $file){
                    if (is_dir($file)){
                        /* strip the store path and recurse */
                        $file = substr($file, strlen($this->_store_path));
                        $this->delete_path($file);
                    }else{
                        unlink($file);
                    }
                }
                rmdir($this->_store_path . $path);
            }
        }
        
        public function get_size($key){
            if ($this->is_key_valid($key)){
                $path = $this->_store_path . $key;
                //$path = $this->_store_path . "private/" . $key;
                if (file_exists($path)){
                    return file_size($path);
                }
            }else{
                $this->error("Invalid file storage key '{$key}'");
            }
            
            return 0;
        }
        
        public function set_content_type($key, $content_type = null){
            $this->set_meta_data($key, 'content_type', $content_type);
        }
        
        public function get_content_type($key){
            return $this->get_meta_data($key, 'content_type');
        }
        
        public function set_meta_data($key, $tag, $value){
            $data = $this->get_meta_data_file($key);
            $data[$tag] = $value;
            $this->set_meta_data_file($key, $data);
        }
        
        public function get_meta_data($key, $tag){
            $data = $this->get_meta_data_file($key);
            if (is_array($data) && isset($data[$tag])){
                return $data[$tag];
            }
            
            return null;
        }
        
        
        public function get_meta_data_file($key){
            if ($this->is_key_valid($key)){
                if (file_exists($this->_store_path . $key . ".meta")){
                    $raw_data = file_get_contents($this->_store_path . $key . ".meta");
                    if ($raw_data){
                        $data = unserialize($raw_data);
                        
                        if ($data && is_array($data)){
                            return $data;
                        }
                    }
                }
            }else{
                $this->error("Invalid file storage key '{$key}'");
            }
            
            return array();
        }
        
        public function set_meta_data_file($key, $data){
            if ($this->is_key_valid($key)){
                $fp = fopen($this->_store_path .  $key . ".meta", "w");
                if ($fp){
                    fwrite($fp, serialize($data));
                    fclose($fp);
                    
                    return true;
                }else{
                    $this->error("Unable to write file {$this->_store_path}{$key}.meta");
                }
            }else{
                $this->error("Invalid file storage key '{$key}'");
            }
        }
    }
    
    
}

