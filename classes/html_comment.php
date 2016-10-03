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
 *
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Create a HTML comment.
     *
     * @property-read integer $type
     * The type of comment
     * @property-read string $comment
     * The comment
     */
    class html_comment extends html{
        
        /** @ignore */
        protected $_comment;
        
        /** @ignore */
        protected $_type;
        
        /**
         * Comment type: Standard
         */
        const STANDARD = 0;
        
        /**
         * Comment type: IE conditional start
         */
        const IE_START = 1;
        
        /**
         * Comment type: IE conditional end
         */
        const IE_END = 2;
        
        /**
         * Contructor
         *
         * @access public
         * @param string
         * The comment
         * @param integer
         * Optional, the type of comment.
         */
        public function __construct($comment, $type = 0){
            parent::__construct("_comment_");
            $this->_comment = $comment;
            $this->_type = $type;
        }
        
        /** @ignore */
        public function pget_type(){
            return $this->_type;
        }
        
        /** @ignore */
        public function pget_comment(){
            return $this->_comment;
        }
        
        /**
         * Renders the element.
         *
         * @access public
         * @param boolean
         * Used internally, should always be null.
         * @param boolean
         * Used internally, should always be null.
         * @param integer
         * Used internally, should always be null. When outputting
         * readable HTML this denotes the depth of the element.
         * @return string
         * The rendered element.
         */
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