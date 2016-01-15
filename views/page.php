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
    
    class page extends html{
        
        protected $_head;
        protected $_body;
        protected $_cache_time = 0;
        
        public function __construct($title = "", $data = null, $attributes = array()){
            $this->_head = new html_head();
            $this->_body = new html_body();
            
            parent::__construct('html', $data, $attributes);
            parent::add($this->_head, $this->_body);
            
            $this->attr('lang', 'en');
            
            if ($title != ""){
                $this->head->add(new html_title($title));
            }
            
            $bundle_adapt = $this->bundles->get_bundle('adapt');
            
            $this->head->add(new html_meta(array('http-equiv' => 'content-type', 'content' => 'text/html;charset=utf-8')));            
            $this->head->add(new html_script(array('type' => 'text/javascript', 'src' => '/_adapt/sanitizers')));
            $this->head->add(new html_script(array('type' => 'text/javascript', 'src' => "/adapt/adapt/adapt-{$bundle_adapt->version}/static/js/adapt.js")));
            $this->head->add(new html_script(array('type' => 'text/javascript', 'src' => "/adapt/adapt/adapt-{$bundle_adapt->version}/static/js/date.js")));
        }
        
        public function aget_cache_time(){
            return $this->_cache_time;
        }
        
        public function aset_cache_time($seconds){
            $this->_cache_time = $seconds;
        }
        
        public function aget_head(){
            return $this->_head;
        }
        
        public function aget_body(){
            return $this->_body;
        }
        
        public function pget_title(){
            return $this->find('title')->text();
        }
        
        public function pset_title($title){
            if ($this->find('head title')->size() == 0){
                $this->find('head')->prepend(new html_title($title));
            }else{
                $this->find('head title')->text($title);
            }
        }
        
        public function pget_description(){
            return $this->find('head meta[name="description"]')->attr('content');
        }
        
        public function pset_description($description){
            if ($this->find('head meta[name="description"]')->size() == 0){
                $this->find('head')->prepend(new html_meta(array('name' => 'description', 'content' => $description)));
            }else{
                $this->find('head meta[name="description"]')->attr('content', $description);
            }
        }
        
        public function pget_keywords(){
            return $this->find('head meta[name="keywords"]')->attr('content');
        }
        
        public function pset_keywords($keywords){
            if ($this->find('head meta[name="keywords"]')->size() == 0){
                $this->find('head')->prepend(new html_meta(array('name' => 'keywords', 'content' => $keywords)));
            }else{
                $this->find('head meta[name="keywords"]')->attr('content', $keywords);
            }
        }
        
        public function add(){
            $this->body->add(func_get_args());
        }
        
        public function render(){
            $this->body->add(new html_comment('Powered by Adapt Framework - http://www.adaptframework.com'));
            return "<!DOCTYPE html>\n" . parent::render();
        }
        
    }
    
}

?>