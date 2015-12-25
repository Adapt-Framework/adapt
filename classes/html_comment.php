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
    
    class html_comment extends html{
        
        protected $_comment;
        protected $_type;
        
        const STANDARD = 0;
        const IE_START = 1;
        const IE_END = 2;
        
        public function __construct($comment, $type = 0){
            parent::__construct("_comment_");
            $this->_comment = $comment;
            $this->_type = $type;
        }
        
        public function aget_type(){
            return $this->_type;
        }
        
        public function aget_comment(){
            return $this->_comment;
        }
        
        public function render($not_req_1 = null, $not_req_2 = null, $depth = 0){
            /*
             * We can ignore the first two input params because
             * these are here only for compatibility with xml
             */
            $readable = $this->setting('xml.readable');
            
            $comment = "";
            
            if ($readable) for($i = 0; $i < $depth; $i++) $comment .= "  ";
            
            $comment .= "<!";
            if ($this->type == 0 || $this->type == 1){
                $comment .= "-- ";
            }
            $comment .= $this->_comment;
            if ($this->type == 0 || $this->type == 2){
                $comment .= " --";
            }
            
            $comment .= ">";
            if ($readable) $comment .= "\n";
            return $comment;
        }
    }
    
}


?>