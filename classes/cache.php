<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
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
 * @package     adapt
 * @author      Matt Bruton <matt.bruton@gmail.com>
 * @copyright   2016 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Local cache
     *
     * Adapt makes an instance of this class available
     * on \adapt\base with the property $cache.
     *
     * @property-read storage_file_system $file_store
     * Holds the local file system used by this class.
     */
    class cache extends base{
        
        /** @ignore */
        protected $_file_store;
        
        /**
         * Contructor
         */
        public function __construct(){
            parent::__construct();
        }
        
        /** @ignore */
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
        
        /**
         * Gets data from the cache with the named $key.
         *
         * If there is no data associated with the key or
         * the data has expired, null is returned.
         *
         * @access public
         * @param string
         * The key for the data to be retrieved.
         * @return mixed
         */
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
        
        /**
         * Returns a cached sql data set.
         *
         * When using \adapt\sql caching is handled on your behalf.
         *
         * @param string
         * The SQL statement
         * @return null|array
         * The dataset or null.
         */
        public function get_sql($key){
            return $this->get("adapt/sql/" . md5($key));
        }
        
        /**
         * Returns the content type of the data identified
         * by the $key
         *
         * @param string
         * @return null|string
         */
        public function get_content_type($key){
            $key = $this->_get_key($key);
            return $this->file_store->get_content_type($key);
        }
        
        
        /**
         * Stores data in the cache.
         *
         * The key should be prefixed '<bundle_name>/' to ensure
         * there is no overlap with other bundles using the cache.
         * 
         * @access public
         * @param string
         * The key to store the data against.
         * @param mixed
         * The data to store
         * @param integer
         * The number of seconds to cache the data for. Defaults to 300 (5 mins)
         * @param string
         * Optional, the content type of the data.
         */
        public function set($key, $data, $expires = 300, $content_type = null){
            if (strtolower($this->setting('adapt.disable_caching')) != 'yes'){
                //print "<pre>{$key} becomes ";
                $key = $this->_get_key($key);
                //print "{$key}</pre>";
                $this->file_store->set($key, $data, $content_type);
                $date = new date();
                $date->goto_seconds($expires);
                $this->file_store->set_meta_data($key, 'expires', $date->date('Y-m-d H:i:s'));
            }
        }
        
        /**
         * Deletes data from the cache
         *
         * @access public
         * @param string
         * The key identifing the data to be removed.
         */
        public function delete($key){
            $key = $this->_get_key($key);
            $this->file_store->delete($key);
        }
        
        /**
         * Caches a SQL result
         *
         * @access public
         * @param string
         * This should be the SQL statement.
         * @param array
         * An array holding the SQL result.
         * @param integer
         * Optiona. How many seconds should the result be cached for?
         * When missing the value held against the setting 'adapt.sql_cache_expires_after'
         * is used.
         */
        public function sql($key, $sql_results = array(), $expires = null){
            $key = "adapt/sql/" . md5($key);
            if (is_null($expires)){
                $expires = $this->setting('adapt.sql_cache_expires_after');
            }
            
            $this->set($key, serialize($sql_results), $expires, 'application/octet-stream');
        }
        
        /**
         * Caches a view
         *
         * @access public
         * @param string
         * The key to identify the view
         * @param string|xml|html|view
         * The view to cache
         * @param integer
         * Optiona. How many seconds should the view be cached for?
         * When missing the value held against the setting 'adapt.view_cache_expires_after'
         * is used.
         */
        public function view($key, $view, $expires = null){
            if (is_null($expires)){
                $expires = $this->setting('adapt.view_cache_expires_after');
            }
            
            $this->set($key, serialize($view), $expires, 'application/octet-stream');
        }
        
        /**
         * Caches a page
         *
         * @access public
         * @param string
         * The key to identify the page
         * @param string|xml|html|view|page
         * The page to cache
         * @param integer
         * Optiona. How many seconds should the page be cached for?
         * When missing the value held against the setting 'adapt.page_cache_expires_after'
         * is used.
         */
        public function page($key, $html, $expires = null){
            if (is_null($expires)){
                $expires = $this->setting('adapt.page_cache_expires_after');
            }
            
            $this->set($key, $html, $expires, 'text/html');
        }
        
        /**
         * Caches serializable data
         *
         * @access public
         * @param string
         * The key to identify the data
         * @param mixed
         * The data to cache
         * @param integer
         * Optiona. How many seconds should the data be cached for?
         */
        public function serialize($key, $object, $expires = null){
            $this->set($key, serialize($object), $expires, 'application/octet-stream');
        }
        
        /** @ignore */
        public function javascript($key, $javascript, $expires = 600, $public = false){
            $this->set($key, $javascript, $expires, 'text/javascript', $public);
        }
        
        /** @ignore */
        public function css($key, $css, $expires = 600, $public = false){
            $this->set($key, $css, $expires, 'text/css', $public);
        }
        
        /** @ignore */
        protected function _get_key($key){
            return "adapt/cache/" . $key;
        }
        
    }
    
}

?>