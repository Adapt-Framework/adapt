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

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class controller_adapt extends controller{
        
        public function view_sanitizers(){
            $this->content_type = 'text/javascript';
            
            $store = $this->store('adapt.sanitizer.data');
            
            $validators = array();
            
            if (isset($store['validators'])){
                foreach($store['validators'] as $name => $validator){
                    $validators[$name] = array(
                        'pattern' => $validator['pattern'],
                        'function' => $validator['js_function']
                    );
                }
            }
            
            $output = "var _adapt_validators = " . json_encode($validators) . ";\n";
            
            $formatters = array();
            
            if (isset($store['formatters'])){
                foreach($store['formatters'] as $name => $formatter){
                    $formatters[$name] = array(
                        'pattern' => $formatter['pattern'],
                        'function' => $formatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_formatters = " . json_encode($formatters) . ";\n";
            
            $unformatters = array();
            
            if (isset($store['unformatters'])){
                foreach($store['unformatters'] as $name => $unformatter){
                    $unformatters[$name] = array(
                        'pattern' => $unformatter['pattern'],
                        'function' => $unformatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_unformatters = " . json_encode($unformatters) . ";\n";
            
            if (!isset($this->request['actions'])){
                $this->cache->javascript($_SERVER['REQUEST_URI'], $output);
            }
            
            return $output;
        }
        
    }
}

?>