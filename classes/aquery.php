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
     * aquery is jQuery for Adapt, allowing rapid manipulation of the DOM before it's sent to the browser.
     *
     * The jQuery documentation is much more detailed than this, you can use the jQuery documentation as
     * a reference but you should be aware of:
     *
     * aquery uses underscores instead of camel case, so addClass in jQuery becomes add_class
     * in aquery.
     * 
     * jQuery's empty() function has been replaced with clear() because 'empty' is a keyword in PHP.
     * 
     * The following jQuery functions are not supported:
     * * wrapAll()
     * * map()
     * * replaceAll()
     * * contents()
     * * untilNext()
     * * parentsUntil()
     * * prevUntil()
     *
     * @property array $elements
     * Gets or sets the current selected elements
     */
    class aquery extends base{
        
        /**
         * Holds the root element
         *
         * @access protected
         * @var html|xml
         */
        protected $_root;
        
        /**
         * Holds the parent element of the current selected
         * element.
         *
         * @access protected
         * @var html|xml
         */
        protected $_parent;
        
        /**
         * Holds the currently selected element(s)
         *
         * @access protected
         * @vars html|xml
         */
        protected $_elements;
        
        
        /**
         * Constructor
         *
         * @access public
         * @param html|xml|view
         * The document to query, leave blank to query the DOM.
         * @param string
         * A well formed CSS selector, or a comma separated list of selectors.
         * @param html|xml|view
         * Optional. The parent of the currently selected element.
         * @param html|xml|view
         * Optional. The root element of the document to query.
         */
        public function __construct($document = null, $selector = null, $parent = null, $root = null){
            parent::__construct();
            
            $this->_elements = array();
            if (is_null($root)){
                $this->_root = $this;
            }else{
                $this->_root = $root;
            }
            
            $this->_parent = $parent;
            
            if (is_null($document)){ $document = $this->dom; }            
            if (!is_array($document)){ $document = array($document); }
            
            foreach($document as $d){
                if (is_string($d)){
                    $d = xml::parse($d);
                }
                
                if ($d instanceof xml){
                    $this->_elements[] = $d;
                }
            }
            
            if ($selector){
                $this->_elements = $this->find($selector)->elements;
            }
        }
        
        /**
         * Property containing an array of currently selected elements.
         *
         * @access public
         * @param array
         * An array of html, xml or view objects.
         */
        public function pset_elements($elements){
            $this->_elements = $elements;
        }
        
        public function pget_elements(){
            return $this->_elements;
        }
        
        
        /**
         * Adds a css class to the matched element(s)
         *
         * @param string
         * The css class name to add.
         * @return aquery
         * Returns itself with the newly modified elements.
         */
        public function add_class($class_name){
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (!$classes) $classes = "";
                if (is_string($classes) && !preg_match("/\b{$class_name}\b/", $classes)){
                    $e->attribute('class', trim($classes . " " . $class_name));
                }
            }
            
            return $this;
        }
        
        /**
         * Adds a CSS propery to the style attribute of the
         * matched element(s) when $value is present, otherwise
         * returns the value (if set).
         *
         * @param string
         * The CSS property to set or get.
         * @param string
         * Optional. The value of the property to set.
         * @return null|string
         * When $value is present, returns null. Otherwise returns
         * the value of the property as it currently is.
         */
        public function css($property, $value = null){
            foreach($this->elements as $e){
                if ($e instanceof xml){
                    $style = $e->attribute('style');
                    if ($style){
                        $final = array();
                        $styles = explode(";", $style);
                        foreach($styles as $style){
                            $style = trim($style);
                            list($key, $key_value) = explode(":", $style);
                            $final[$key] = $key_value;
                        }
                        if (!$value){
                            return $final[$property];
                        }else{
                            $final[$property] = $value;
                            $styles = "";
                            foreach($final as $key => $value){
                                if ($key) $styles .= "{$key}:{$value};";
                            }
                            $e->attribute('style', $styles);
                        }
                    }else{
                        if ($value){
                            $e->attribute('style', "{$property}:{$value};");
                        }
                    }
                }
            }
        }
        
        /**
         * Removes a CSS class from the matched elements
         *
         * @param string
         * The CSS class name to be removed
         * @return aquery
         * Returns itself with the modified elements.
         */
        public function remove_class($class_name){
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (is_string($classes) && preg_match("/\b{$class_name}\b/", $classes)){
                    $e->attribute('class', preg_replace("/\b{$class_name}\b/", "", $classes));
                    $e->attribute('class', trim($e->attribute('class')));
                }
            }
            
            return $this;
        }
        
        /**
         * Tests if the CSS class name exists in the selected
         * elements
         *
         * @param string
         * CSS class name
         * @return boolean
         */
        public function has_class($class_name){
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (is_string($classes)){
                    return preg_match("/\b{$class_name}\b/", $classes);
                }
            }
            
            return false;
        }
        
        /**
         * Toggle a CSS class
         *
         * @param string
         * The CSS class name to toggle.
         * @param boolean
         * Optional. When true the class should only be added, when false
         * the class should only be removed.  When absent or null the class
         * will be added if it isn't, or removed if it is.
         * @return aquery
         */
        public function toggle_class($class_name, $switch = null){
            foreach($this->elements as $e){
                if ($switch === true || $switch === false){
                    $a = new aquery($e);
                    if ($switch != $a->has_class($class_name)){
                        if ($a->has_class($class_name)){
                            $a->remove_class($class_name);
                        }else{
                            $a->add_class($class_name);
                        }
                    }
                }else{
                    if ($a->has_class($class_name)){
                        $a->remove_class($class_name);
                    }else{
                        $a->add_class($class_name);
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Returns or sets the text in the matched elements.
         *
         * @param string
         * Optional. When provided the text is added to the element.
         * When null the text from the element is returned.
         * @return null|string
         */
        public function text($text = null){
            if (is_string($text)){
                foreach($this->elements as $e){
                    for($i = 0; $i < count($e->get()); $i++){
                        if (!is_object($e->get($i)) && is_string($e->get($i)) && !$e->get($i) instanceof xml){
                            $e->clear();
                            $e->add($text);
                        }
                    }
                }
            }else{
                $output = "";
                foreach($this->elements as $e){
                    foreach($e->get() as $child){
                        if (!is_object($child) && is_string($child) && !$child instanceof xml){
                            $output .= $child;
                        }elseif ($child instanceof xml){
                            $a = new aquery($child);
                            $output .= $a->text();
                        }
                    }
                }
                return $output;
            }
        }
        
        /**
         * Get the HTML contents of the first element in the set of matched elements or
         * set the HTML contents of every matched element.
         *
         * @param string
         * HTML to set
         * @return string
         * Returns the HTML of the first matched element
         */
        public function html($string = null){
            if (is_string($string)){
                return $this->html(html::parse($string));
            }elseif ($string instanceof html){
                foreach($this->elements as $e){
                    $e->children = array($string);
                }
            }else{
                if (count($this->elements) > 0){
                    $output = "";
                    foreach($this->elements[0]->children as $child){
                        if ($child instanceof html) $output .= $child;
                    }
                    
                    return $output;
                }
            }
        }
        
        /**
         * Get the current value of the first element in the set of matched elements or
         * set the value of every matched element.
         *
         * @param string
         * The value to set
         * @return string
         * The value of the first matched element.
         */
        public function val($value = null){
            if ($value){
                foreach($this->elements as $e){
                    $e->attribute('value', $value);
                }
            }else{
                foreach($this->elements as $e){
                    return $e->attribute('value');
                }
            }
        }
        
        /**
         * Get the value of an attribute for the first element in the set of matched
         * elements or set one or more attributes for every matched element.
         *
         * @param string
         * Attribute name
         * @param string
         * Optional. Attribute value
         * @return string
         * Returns the attribute value of the first matched element.
         */
        public function attr($attribute, $value = null){
            foreach($this->elements as $e){
                if (!isset($value)){
                    return $e->attribute($attribute);
                }else{
                    $e->attribute($attribute, $value);
                }
            }
            
            return "";
        }
        
        /**
         * Removes an attribute
         *
         * @param string
         * Attribute to remove
         * @return aquery
         * Returns itself.
         */
        public function remove_attr($attribute){
            $attributes = explode(" ", $attribute);
            foreach($attributes as $a){
                $a = trim($a);
                foreach($this->elements as $e){
                    $e->remove_attribute($a);
                }
            }
            
            return $this;
        }
        
        /**
         * Inserts content after the matched element(s)
         *
         * @param string|xml|html|view
         * The content to add
         * @return aquery
         * Returns self.
         */
        public function after($content){
            foreach($this->elements as $e){
                if ($e->parent instanceof xml){
                    $found = false;
                    $final = array();
                    for($i = 0; $i < $e->parent->count(); $i++){
                        $final[] = $e->parent->get($i);
                        if ($e->parent->get($i) === $e){
                            $final[] = $content;
                        }
                    }
                    $e->parent->clear();
                    foreach($final as $f) $e->parent->add($f);
                }
            }
            
            return $this;
            
            //From orginal framework before xml was changed
            //foreach($this->elements as $e){
            //    if ($e->parent instanceof xml){
            //        $found = false;
            //        $final = array();
            //        for($i = 0; $i < count($e->parent->children); $i++){
            //            $final[] = $e->parent->children[$i];
            //            if ($e->parent->children[$i] === $e){
            //                $final[] = $content;
            //            }
            //        }
            //        $e->parent->children = array();
            //        foreach($final as $f) $e->parent->add($f);
            //    }
            //}
            //
            //return $this;
        }
        
        /**
         * Insert content, specified by the parameter, to the end of each element
         * in the set of matched elements.
         *
         * @param string|xml|html|view
         * The content to add
         * @return aquery
         * Returns self.
         */
        public function append($content){
            foreach($this->elements as $e){
                if ($e instanceof xml){
                    $e->_add($content);
                }
            }
            
            return $this;
        }
        
        /**
         * Insert content, specified by the parameter, before each element in the
         * set of matched elements.
         * 
         * @param string|xml|html|view
         * The content to add
         * @return aquery
         * Returns self.
         */
        public function before($content){
            if (!is_array($content)) $content = array($content);
            foreach($this->elements as $e){
                if ($e->parent instanceof xml){
                    $children = $e->parent->get();
                    $e->parent->clear();
                    foreach($children as $child){
                        if ($child === $e){
                            $e->parent->_add($content);
                        }
                        $e->parent->_add($child);
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Remove the set of matched elements from the DOM.
         * @param string
         * Optional. CSS selector.
         * @return aquery
         * A new aquery object containing the removed elements.
         */
        public function detach($selector = null){
            if ($selector) $selector = $this->sanitize_selector($selector);
            $output = array();
            
            foreach($this->elements as $e){
                if ($e instanceof xml){
                    if (is_array($selector)){
                        foreach($selector as $s){
                            if ($this->matches_selector($s, $e)){
                                $output[] = $e;
                                if ($e->parent instanceof xml){
                                    $e->parent->remove($e);
                                }
                            }
                        }
                    }else{
                        $output[] = $e;
                        if ($e->parent instanceof xml){
                            $e->parent->remove($e);
                        }
                    }
                    
                }
            }
            
            return new aquery($output);
        }
        
        /**
         * Removes all children from the DOM.
         * 
         * Renamed from 'empty' because it
         * is a keyword in php.
         *
         * @return aquery
         * Returns self.
         */
        public function clear(){
            foreach($this->elements as $e){
                $e->clear();
            }
            
            return $this;
        }
        
        
        /**
         * Insert content, specified by the parameter, to the beginning of each element
         * in the set of matched elements.
         *
         * @param string|xml|html|view
         * The content to prepend.
         * @return aquery
         * Returns self.
         */
        public function prepend($content){
            foreach($this->elements as $e){
                if ($e instanceof xml){
                    $children = $e->get();
                    $e->clear();
                    $e->_add($content);
                    $e->_add($children);
                }
            }
            return $this;
        }
        
        /**
         * Remove the set of matched elements from the DOM.
         *
         * @param string
         * Optional CSS selector
         * @return aquery
         * Returns self.
         */
        public function remove($selector = null){
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                $final = array();
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($this->matches_selector($s, $e)){
                            if ($e->parent instanceof xml){
                                $e->parent->remove($e);
                            }
                        }else{
                            $final[] = $e;
                        }
                    }
                }
                $this->_elements = $final;
            }else{
                foreach($this->elements as $e){
                    if ($e->parent instanceof xml){
                        $e->parent->remove($e);
                    }
                }
                $this->_elements = array();
            }
            
            return $this;
        }
        
        /**
         * Replace each element in the set of matched elements with the provided
         * new content and return the set of elements that was removed.
         *
         * @param string|xml|html|view
         * @return aquery
         * Returns self.
         */
        public function replace_with($content){
            foreach($this->elements as $e){
                if ($e->parent instanceof xml){
                    $children = $e->parent->get();
                    $e->parent->clear();
                    
                    foreach($children as $child){
                        if ($child === $e){
                            $e->parent->add($content);
                        }else{
                            $e->parent->add($child);
                        }
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Remove the parents of the set of matched elements from the DOM, leaving the matched
         * elements in their place.
         *
         * @access public
         * @return aquery
         * Returns self
         */
        public function unwrap(){
            foreach ($this->elements as $e){
                if ($e->parent instanceof xml && $e->parent->parent instanceof xml){
                    $children = $e->parent->parent->get();
                    $e->parent->parent->clear();
                    foreach($children as $c){
                        if ($c === $e->parent){
                            $e->parent->parent->add($e);
                        }else{
                            $e->parent->parent->add($c);
                        }
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Wrap an HTML structure around each element in the set of matched elements.
         *
         * @access public
         * @param string|xml|html|view
         * The HTML structure to act as a wrapper.
         * @return aquery
         * Returns self
         */
        public function wrap($wrapper){
            if ($wrapper instanceof xml){
                $wrapper = strval($wrapper->render());
            }
            
            foreach($this->elements as $e){
                $node = xml::parse($wrapper);
                
                if ($e->parent instanceof xml){
                    $children = $e->parent->get();
                    $parent = $e->parent;
                    $e->parent->clear();
                    
                    foreach($children as $c){
                        if ($c === $e){
                            $node->add($e);
                            $parent->_add($node);
                        }else{
                            $parent->_add($c);
                        }
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Wrap an HTML structure around the content of each element in the set of matched
         * elements.
         *
         * @access public
         * @param string|xml|html|view
         * The content to act as a wrapper.
         * @return aquery
         * Returns self
         */
        public function wrap_inner($wrapper){
            if ($wrapper instanceof xml) $wrapper = strval($wrapper->render());
            
            foreach($this->elements as $e){
                $node = xml::parse($wrapper);
                foreach($e->get() as $c){
                    $node->add($c);
                }
                $e->clear();
                $e->add($node);
            }
            
            return $this;
        }
        
        /**
         * Retrieve the DOM elements matched by the jQuery object.
         *
         * @access public
         * @param integer $index
         * Optional. When specified returns the element at $index
         * @return array|string|xml|html|view
         * Returns the element at $index or an array of elements.
         */
        public function get($index = null){
            if (!is_null($index)){
                if ($index >= 0 && $index < count($this->elements)){
                    return $this->elements[$index];
                }elseif($index < 0){
                    $i = count($this->elements) + $index;
                    if ($i >= 0 && $i < count($this->elements)){
                        return $this->elements[$i];
                    }
                }
            }
            
            return $this->elements;
        }
        
        /**
         * Returns all the matched elements as an array
         *
         * @access public
         * @return array
         * Returns an array of matched elements.
         */
        public function to_array(){
            return $this->_elements;
        }
        
        /**
         * Returns a count of matched elements
         *
         * @access public
         * @return integer
         */
        public function size(){
            return count($this->_elements);
        }
        
        /**
         * Reduce the set of matched elements to the one at the specified index.
         *
         * @access public
         * @param integer $index
         * The index of the element
         * @return aquery
         * Returns either itself or a new aquery object
         */
        public function eq($index){
            if (!is_null($index)){
                if ($index >= 0 && $index < count($this->elements)){
                    return new aquery($this->elements[$index], null, $this, $this->_root);
                }elseif($index < 0){
                    $i = count($this->elements) + $index;
                    
                    if ($i >= 0 && $i < count($this->elements)){
                        return new aquery($this->elements[$i], null, $this, $this->_root);
                    }
                }
            }
            
            return $this;
        }
        
        /**
         * Reduce the set of matched elements to those that match the selector.
         *
         * @access public
         * @param string $selector
         * The CSS selector to use to filter the set
         * @return aquery
         * Returns an aquery object containing the matched set
         */
        public function filter($selector){
            $selector = $this->sanitize_selector($selector);
            $output = array();
            
            foreach($selector as $s){
                foreach($this->elements as $e){
                    if ($this->matches_selector($s, $e) && !in_array($e, $output)){
                        $output[] = $e;
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get the descendants of each element in the current set of matched elements filtered by a selector.
         *
         * @access public
         * @param string $selector
         * A CSS selector to filter the elements
         * @return aquery
         * Returns a new aquery object containing the foltered set.
         */
        public function find($selector){
            $selector = $this->sanitize_selector($selector);
            $output = array();
            
            foreach($selector as $s){
                foreach($this->elements as $element){
                    
                    foreach($element->get() as $child){
                        if ($this->matches_selector($s, $child)){
                            
                            if (($s->parent instanceof selector && $s->parent->child_relationship == selector::RELATIONSHIP_DIRECT && $this->matches_selector($s->parent, $element)) || (!$s->parent) || ($s->parent->child_relationship == selector::RELATIONSHIP_DISTANT)){
                                if ($s->child instanceof selector){
                                    /* We have a child selector so we are going to pass it along */
                                    $a = new aquery($child, $s->child);
                                    $output = array_merge($output, $a->elements);
                                }else{
                                    /* End of the line and it matches */
                                    $output[] = $child;
                                }
                            }
                        }
                        $a = new aquery($child, $s);
                        $output = array_merge($output, $a->elements);
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Returns the first element in the set
         *
         * @access public
         * @return string|xml|html|view
         */
        public function first(){
            return $this->eq(0);
        }
        
        /**
         * Reduce the set of matched elements to those that have a descendant that matches
         * the selector.
         *
         * @access public
         * @param string $selector
         * The CSS selector used to filter.
         * @return aquery
         * Returns a new aquery object
         */
        public function has($selector){
            $selector = $this->sanitize_selector($selector);
            $output = array();
            foreach($selector as $s){
                foreach($this->elements as $e){
                    $q = new aquery($e, $s);
                    if ($q->size() > 0){
                        $output[] = $e;
                    }
                }
            }
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Check the current matched set of elements against a selector and return true if at least one of
         * these elements matches the given arguments.
         *
         * @access public
         * @param string $selector
         * A CSS selector
         * @return boolean
         */
        public function is($selector){
            $selector = $this->sanitize_selector($selector);
            foreach($selector as $s){
                foreach($this->elements as $e){
                    if ($this->matches_selector($s, $e)){
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        /**
         * Returns the last element in the set.
         *
         * @access public
         * @return string|xml|html|view
         */
        public function last(){
            return $this->eq(-1);
        }
        
        /**
         * Remove elements from the set of matched elements.
         *
         * @access public
         * @param string $selector
         * A CSS selector
         * @return aquery
         */
        public function not($selector){
            $selector = $this->sanitize_selector($selector);
            $output = array();
            
            foreach($selector as $s){
                foreach($this->elements as $e){
                    if (!$this->matches_selector($s, $e)){
                        $output[] = $e;
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Reduce the set of matched elements to a subset specified by a range of indices.
         *
         * @access public
         * @param integer $start
         * The start index
         * @param integer $end
         * Optional. The end index
         * @return aquery
         */
        public function slice($start, $end = null){ //TESTED
            if ($start < 0) $start = count($this->elements) + $start;
            if (is_null($end)) $end = count($this->elements) - 1;
            if ($end < 0) $end = count($this->elements) + $end;
            
            if ($start > $end){
                $temp = $start;
                $start = $end;
                $end = $temp;
            }
            
            if ($start >= count($this->elements)) $start = 0;
            if ($end >= count($this->elements)) $end = count($this->elements) - 1;
            
            $output = array();
            
            for($i = 0; $i < count($this->elements); $i++){
                if ($i >= $start && $i <= $end){
                    $output[] = $this->elements[$i];
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Create a new aquery object with elements added to the set of matched elements.
         *
         * @access public
         * @param string $selector
         * A CSS selector
         * @return aquery
         */
        public function add($selector){ //TESTED
            $output = $this->elements;
            
            $q = new aquery($this->_root->elements, $selector, $this, $this->_root);
            $output = array_merge($output, $q->get());
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Add the previous set of elements on the stack to the current set.
         *
         * @access public
         * @return aquery
         */
        public function and_self(){ //TESTED - But I'm not sure if I understand the documentation on jQuery...
            $output = $this->_elements;
            
            if ($this->_parent instanceof aquery){
                $output = array_merge($output, $this->_parent->elements);
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * End the most recent filtering operation in the current chain and return the set of matched
         * elements to its previous state.
         *
         * @access public
         * @return aquery
         */
        public function end(){
            if ($this->_parent instanceof aquery){
                return $this->_parent;
            }
            
            return $this;
        }
        
        /**
         * Get the children of each element in the set of matched elements, optionally filtered
         * by a selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector to filter the children
         * @return aquery
         */
        public function children($selector = null){
            $output = array();
            
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e instanceof xml){
                            foreach($e->get() as $c){
                                if ($c instanceof xml){
                                    if ($this->matches_selector($s, $c)){
                                        $output[] = $c;
                                    }
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e instanceof xml){
                        foreach($e->get() as $c){
                            if ($c instanceof xml){
                                $output[] = $c;
                            }
                        }
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * For each element in the set, get the first element that matches the selector by testing the element
         * itself and traversing up through its ancestors in the DOM tree.
         *
         * @access public
         * @param string $selector
         * A CSS selector
         * @return aquery
         */
        public function closest($selector){
            $selector = $this->sanitize_selector($selector);
            
            foreach($selector as $s){
                foreach($this->elements as $e){
                    while ($e instanceof xml){
                        if ($this->matches_selector($s, $e)){
                            return new aquery($e, null, $this, $this->_root);
                        }else{
                            $e = $e->parent;
                        }
                    }
                }
            }
            
            return new aquery(array(), null, $this, $this->_root);
        }
        
        /**
         * Get the immediately following sibling of each element in the set of matched elements. If a selector is
         * provided, it retrieves the next sibling only if it matches that selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector
         * @return aquery
         */
        public function next($selector = null){
            $output = array();
            
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e instanceof xml && $e->parent instanceof xml){
                            $found = false;
                            foreach($e->parent->get() as $c){
                                if ($found){
                                    if ($this->matches_selector($s, $c)){
                                        $output[] = $c;
                                    }
                                    break;
                                }
                                if ($c === $e){
                                    $found = true;
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e instanceof xml && $e->parent instanceof xml){
                        $found = false;
                        foreach($e->parent->get() as $c){
                            if ($found){
                                $output[] = $c;
                                break;
                            }
                            if ($c === $e){
                                $found = true;
                            }
                        }
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get all following siblings of each element in the set of matched elements, optionally filtered
         * by a selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector to filter the siblings.
         * @return aquery
         */
        public function next_all($selector = null){
            $output = array();
            
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e instanceof xml && $e->parent instanceof xml){
                            $found = false;
                            foreach($e->parent->get() as $c){
                                if ($found){
                                    if ($this->matches_selector($s, $c)){
                                        $output[] = $c;
                                    }
                                }
                                if ($c === $e){
                                    $found = true;
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e instanceof xml && $e->parent instanceof xml){
                        $found = false;
                        foreach($e->parent->get() as $c){
                            if ($found){
                                $output[] = $c;
                            }
                            if ($c === $e){
                                $found = true;
                            }
                        }
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get the parent of each element in the current set of matched elements, optionally filtered
         * by a selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector to filter the parent(s)
         * @return aquery
         */
        public function parent($selector = null){
            $output = array();
            
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e instanceof xml && $e->parent instanceof xml){
                            if ($this->matches_selector($s, $e->parent)){
                                $output[] = $e->parent;
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e instanceof xml && $e->parent instanceof xml){
                        $output[] = $e->parent;
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get the ancestors of each element in the current set of matched elements, up to but not including the
         * element matched by the selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector
         * @return aquery
         */
        public function parents($selector = null){
            $output = array();
            
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        $e = $e->parent;
                        while ($e instanceof xml){
                            if ($this->matches_selector($s, $e)){
                                $output[] = $e;
                            }
                            $e = $e->parent;
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    $e = $e->parent;
                    while ($e instanceof xml){
                        $output[] = $e;
                        $e = $e->parent;
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get the immediately preceding sibling of each element in the set of matched elements. If a
         * selector is provided, it retrieves the previous sibling only if it matches that selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector
         * @return aquery
         */
        public function prev($selector = null){
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e->parent instanceof xml){
                            $prev = null;
                            foreach($e->parent->get() as $c){
                                if ($c === $e){
                                    return new aquery($prev, null, $this, $this->_root);
                                }elseif ($this->matches_selector($s, $c)){
                                    $prev = $c;
                                }else{
                                    $prev = null;
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e->parent instanceof xml){
                        $prev = null;
                        foreach($e->parent->get() as $c){
                            if ($c === $e){
                                return new aquery($prev, null, $this, $this->_root);
                            }else{
                                $prev = $c;
                            }
                        }
                    }
                }
            }
            
            return new aquery(null, null, $this, $this->_root);
        }
        
        /**
         * Get all preceding siblings of each element in the set of matched elements, optionally
         * filtered by a selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector
         * @return aquery
         */
        public function prev_all($selector = null){
            $output = array();
            if ($selector){
                $selector = $this->sanitize_selector($selector);
                foreach($selector as $s){
                    foreach($this->elements as $e){
                        if ($e->parent instanceof xml){
                            foreach($e->parent->get() as $c){
                                if ($c === $e){
                                    return new aquery($output, null, $this, $this->_root);
                                }elseif ($this->matches_selector($s, $c)){
                                    $output[] = $c;
                                }
                            }
                        }
                    }
                }
            }else{
                foreach($this->elements as $e){
                    if ($e->parent instanceof xml){
                        foreach($e->parent->get() as $c){
                            if ($c === $e){
                                return new aquery($output, null, $this, $this->_root);
                            }else{
                                $output[] = $c;
                            }
                        }
                    }
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /**
         * Get the siblings of each element in the set of matched elements, optionally filtered by
         * a selector.
         *
         * @access public
         * @param string $selector
         * Optional CSS selector
         * @return aquery
         */
        public function siblings($selector = null){
            $prev = $this->prev_all($selector);
            $next = $this->next_all($selector);
            
            return new aquery(array_merge($prev->elements, $next->elements), null, $this, $this->_root);
        }
        
        /**
         * A generic iterator function, which can be used to seamlessly iterate over both objects and
         * arrays. Arrays and array-like objects with a length property (such as a function’s arguments
         * object) are iterated by numeric index, from 0 to length-1. Other objects are iterated via
         * their named properties.
         *
         * @access public
         * @param callable $function
         */
        public function each($function){
            if (is_callable($function) && is_object($function)){
                for($i = 0; $i < count($this->elements); $i++){
                    $function($i, $this->elements[$i]);
                }
            }
        }
        
        
        /**
         * Internal function to test if an element matches a selector.
         *
         * @access protected
         * @param selector $selector
         * A selector objected
         * @param xml|html|view
         * The element to test
         * @return boolean
         * Returns true if the element matches, otherwise returns false
         */
        protected function matches_selector($selector, $xml_node){
            if ($selector instanceof selector && $xml_node instanceof xml){
                if (($selector->tag && strtolower($selector->tag) == strtolower($xml_node->tag)) || !$selector->tag){
                    foreach($selector->attributes as $attr){
                        switch($attr['match']){
                            
                        case selector::ATTRIBUTE_CONTAINS_PREFIX_SELECTOR:
                            $parts = preg_split("/-|_/", $xml_node->attribute($attr['key']));
                            if (strtolower($parts[0]) != strtolower($attr['value'])) return false;
                            break;
                        
                        case selector::ATTRIBUTE_CONTAINS_SELECTOR:
                            if (strpos(strtolower($xml_node->attribute($attr['key'])), $attr['value']) !== false) return false;
                            break;
                            
                        case selector::ATTRIBUTE_CONTAINS_WORD_SELECTOR:
                            if (!preg_match("/\b{$attr['value']}\b/i", $xml_node->attribute($attr['key']))) return false;
                            break;
                            
                        case selector::ATTRIBUTE_ENDS_WITH_SELECTOR:
                            if (!preg_match("/{$attr['value']}$/", $xml_node->attribute($attr['key']))) return false;
                            break;
                            
                        case selector::ATTRIBUTE_EQUALS_SELECTOR:
                            if (strtolower($xml_node->attribute($attr['key'])) != strtolower($attr['value'])) return false;
                            break;
                            
                        case selector::ATTRIBUTE_NOT_EQUAL_SELECTOR:
                            if (strtolower($xml_node->attribute($attr['key'])) == strtolower($attr['value'])) return false;
                            break;
                        
                        case selector::ATTRIBUTE_STARTS_WITH_SELECTOR:
                            if (!preg_match("/^{$attr['value']}/", $xml_node->attribute($attr['key']))) return false;
                            break;
                        
                        case selector::IS_EMPTY:
                            if (count($xml_node->children)) return false;
                            
                        case selector::ALL:
                            return true;
                            break;
                        
                        case selector::EVEN:
                            if ($xml_node->parent && $xml_node->parent instanceof xml){
                                $is_even = false;
                                for($i = 0; $i < $xml_node->parent->count(); $i++){
                                    if ($xml_node === $xml_node->parent->get($i)){
                                        if ($i % 2 == 0)  $is_even = true;
                                    }
                                }
                                if (!$is_even) return false;
                            }
                            break;
                        
                        case selector::ODD:
                            if ($xml_node->parent && $xml_node->parent instanceof xml){
                                $is_even = false;
                                for($i = 0; $i < $xml_node->parent->count(); $i++){
                                    if ($xml_node === $xml_node->parent->get($i)){
                                        if ($i % 2 == 0)  $is_even = true;
                                    }
                                }
                                if ($is_even) return false;
                            }
                            break;
                        default :
                            break;
                        
                        }
                    }
                    
                    return true;
                }
                
            }
            
            return false;
        }
        
        /**
         * Internal function to sanitize and parse selectors
         *
         * @access protected
         * @param string|string[] $selectors
         * A CSS selector or array of CSS selectors
         * @return selector[]
         * Returns an array of selector objects.
         */
        protected function sanitize_selector($selectors){
            $output = array();
            
            if (!is_array($selectors)) $selectors = array($selectors);
            
            foreach($selectors as $s){
                if (is_string($s)){
                    $output = array_merge(selector::parse($s), $output);
                }elseif ($s instanceof selector){
                    $output[] = $s;
                }
            }
            
            return $output;
        }
    }

}

