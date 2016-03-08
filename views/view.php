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
    
    class view extends html{
        
        /* Basic variable swapout */
        protected $_variables;
        
        public function __construct($tag = 'div', $data = null, $attributes = array()){
            $this->_variables = array();
            parent::__construct($tag, $data, $attributes);
            $this->add_class('view');
            $class_name = array_pop(explode("\\", get_class($this)));
            if (preg_match("/^view_/", $class_name)) $class_name = mb_substr($class_name, 5);
            $class_name = str_replace("_", "-", $class_name);
            $this->add_class($class_name);
            //$this->set_id();
        }
        
        public function set_variables($vars){
            $this->_variables = $vars;
        }
        
        public function render($not_req_1 = null, $not_req_2 = null, $depth = 0){
            $output = parent::render($not_req_1, $not_req_2, $depth);
            
            if (is_array($this->_variables) && count($this->_variables)){
                foreach($this->_variables as $key => $value){
                    //print $value;
                    //print "{{" . $key . "}}";
                    $output = str_replace("{{" . $key . "}}", $value, $output);
                }
            }
            
            //$output = preg_replace("/{{[^}]+}}/", "", $output);
            
            return $output;
        }
        
    }

}

?>