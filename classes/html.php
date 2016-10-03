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
     * Foundation class for handling html.
     *
     * This class has been registered as a class handler.
     *
     * Example of creating a div tag without using this class
     * as a class handler.
     *
     * <code>
     * $div = new html('div');
     * </code>
     *
     * Example of creating a div tag using this class as a
     * class handler.
     *
     * <code>
     * $div = new html_div();
     * </code>
     *
     * @property string $id
     * Gets or sets the HTML id attribute
     */
    class html extends xml{
        
        /** @ignore */
        protected $_closed_tags;
        
        /**
         * Constructor
         *
         * @access public
         * @param string
         * The tag to create
         * @param string|html|view
         * Optionally the data to add.
         * @param array
         * An associative array of attributes.
         */
        public function __construct($tag = null, $data = null, $attributes = array()){
            $this->_closed_tags = $GLOBALS['adapt']->setting('html.closed_tags');
            parent::__construct($tag, $data, $attributes, !in_array(strtolower($tag), $this->_closed_tags));
            
        }
        
        /**
         * Sets the ID attribute
         *
         * @access public
         * @param string
         * Optional, when provided the ID is set to this value, else the ID is
         * auto generated.
         */
        public function set_id($id = null){
            if (is_null($id)) $id = "adapt-id-" . $this->instance_id;
            $this->attribute('id', $id);
        }
        
        /** @ignore */
        public function pget_id(){
            return $this->attribute('id');
        }
        
        /** @ignore */
        public function pset_id($id){
            $this->attribute('id', $id);
        }
        
        /**
         * Add a HTML class to the element.
         *
         * @access publc
         * @param string
         * The class to add.
         */
        public function add_class($class){
            if (is_array($class)) foreach ($class as $c) return $this->add_class($c);
            $class = mb_trim($class);
            $classes = $this->attribute('class');
            $classes = explode(" ", $classes);
            if (!in_array($class, $classes)) $classes[] = $class;
            $classes = mb_trim(implode(" ", $classes));
            $this->attribute('class', $classes);
        }
        
        /**
         * Removes a HTML class from the element
         *
         * @access public
         * @param string
         * The class to remove from the element.
         */
        public function remove_class($class){
            if (is_array($class)) foreach ($class as $c) return $this->remove_class($c);
            $classes = $this->attribute('class');
            $classes = explode(" ", $classes);
            $temp = array();
            foreach($classes as $c) if ($c != $class) $temp[] = $c;
            $classes = implode(" ", $temp);
            $this->attribute('class', $classes);
        }
        
        /**
         * Checks if the element has the class.
         *
         * @access public
         * @param string
         * The class to check.
         */
        public function has_class($class){
            $classes = $this->attribute('class');
            $classes = explode(" ", $classes);
            return in_array($class, $classes);
        }
        
        /**
         * Renders an attribute
         *
         * @access
         * @param string
         * The attribute name
         * @param string
         * The attribute value
         * @return string
         * The rendered attribute.
         */
        public function render_attribute($key, $value = null){
            if (is_null($value) || $value == ""){
                return $key;
            }
            
            return parent::render_attribute($key, $value);
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
            /**
             * We are going to override render to output html 5
             * instead of pure xml
             */
            if ($this->setting('html.format') == "xhtml"){
                return parent::_render(true, true, $depth);
            }
            return parent::_render(false, false, $depth);
        }
        
        /**
         * Parses a HTML string
         *
         * @access public
         * @param string
         * The HTML to be parsed.
         * @param boolean
         * Should the returned string be output as a document. **Warning** this
         * should always be set to **false**.
         * @return html
         */
        public static function parse($data, $return_as_document = false){
            /*
             * We are going to override the parse function
             * to deal with html 5 style tags that miss
             * the closing slash ie, <... /> to <...>
             */
            $output = null;
            
            if (is_string($data)){
                /* Convert the data to an array */
                /* Remove xml tag & doc type */
                $data = preg_replace("/<\?.*\?>\s?/", "", $data);
                $data = preg_replace("/<!.*>\s?/", "", $data);
                
                /* Split the tags */
                $data = preg_split("/</", $data);
                
                /* Remove empty elements */
                $final = array();
                $h = new html();
                
                foreach($data as $element){                   
                    if (!preg_match("/^\s*$/", $element)){
                        foreach($h->_closed_tags as $tag){
                            if (mb_strlen($element) >= mb_strlen($tag)){
                                if (strtolower(mb_substr($element, 0, mb_strlen($tag))) == $tag){
                                    if (!preg_match("/\/\s*>/", $element)){
                                        $element = preg_replace("/>/", " />", $element);
                                        break;
                                    }
                                }
                            }
                        }
                        $final[] = $element;
                    }
                }
                
                $data = $final;
            }
            if ($return_as_document){
                $output = xml::parse($data, false, new \application\html_document()); //ISSUE: Ummm... This class doesn't exist!
            }else{
                $output = xml::parse($data);
            }
            
            /*
             * We need to fix tags such as <div />
             * and change them to <div></div>
             */
            if (is_array($output)){
                for($i = 0; $i < count($output); $i++){
                    $output[$i] = \application\html::fix_parsed_tags($output[$i]); //ISSUE: Application namespace?
                }
            }else{
                $output = \application\html::fix_parsed_tags($output); //ISSUE: Application namespace?
            }
            
            return $output;
        }
        
        /**
         * Converts HTML5 single tags such as <input> to <input></input> so
         * it is compatable with XML and can be parsed by xml::parse.
         *
         * @access public
         * @param xml
         * The xml to be fixed
         * @return html
         */
        public static function fix_parsed_tags($item){
            $closed_tags = $GLOBALS['adapt']->setting('html.closed_tags');
            
            if ($item instanceof xml){
                /* We need to convert this tag to html */
                
                $node = new html($item->tag, $item->get(), $item->attributes);
                
                $item = $node;
            }
            
            /* Convert the children */
            if ($item instanceof html){ 
                for($i = 0; $i < $item->count(); $i++){
                    if ($item->get($i) instanceof xml){
                        $item->set($i, html::fix_parsed_tags($item->get($i)));
                    }
                }
            }
            
            return $item;
        }
        
        /**
         * Is the $string html?
         *
         * @access public
         * @param string
         * A string
         * @return boolean
         */
        public static function is_html($string){
            $string = trim($string);
            $pattern = "/^<(.*\s*)*>$/";
            
            return preg_match($pattern, $string);
        }
        
    }
    
    

}

?>