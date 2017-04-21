<?php

/* 
 * Adapt Framework (www.adaptframework.com)
 * 
 * The MIT License (MIT)
 *   
 * Copyright (c) 2017 Matt Bruton
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
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class html_document extends html{
        
        public function __construct($data = null, $attributes = array(), $closing_tag = false){
            parent::__construct('html', $data, $attributes, $closing_tag);
        }
        
        public function render($not_req_1 = null, $not_req_2 = null, $depth = 0){
            $output = "<!DOCTYPE html>\n";
            
            /**
             * We are going to override render to output html 5
             * instead of pure xml
             */
            if ($this->setting('html.format') == "xhtml"){
                $output .= parent::_render(true, true, $depth);
            }else{
                $output .= parent::_render(false, false, $depth);
            }
            return $output;
        }
    }
}

?>