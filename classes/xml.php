<?php

/*
 * Adapt Framework (www.adaptframework.com)
 * 
 * The MIT License (MIT)
 *   
 * Copyright (c) 2017 Matt Bruton (www.adaptframework.com)
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

    use Exception;
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class xml extends base{
        
        /* Events */
        const EVENT_RENDER = 'adapt.render';
        const EVENT_RENDERED = 'adapt.rendered';
        const EVENT_CHILD_ADDED = ' adapt.child_added';
        const EVENT_CHILD_REMOVED = 'adapt.child_removed';
        
        /* Private properties */
        protected $_children;
        protected $_namespace;
        protected $_tag;
        protected $_closing_tag;
        protected $_attributes;
        protected $_parent;
        protected $_cdata_tokens;
        
        /*
         * Constructor
         */
        public function __construct($tag = null, $data = null, $attributes = array(), $closing_tag = false){
            parent::__construct();
            
            $this->tag = $tag;
            $this->_children = array();
            $this->_attributes = array();
            $this->_closing_tag = $closing_tag;
            $this->_cdata_tokens = array();
            
            if (is_array($data) && is_assoc($data) && count($attributes) == 0){
                $this->_attributes = $data;
            }else{
                $this->_attributes = $attributes;
                $this->add($data);
            }
        }
        
        /*
         * Properties
         */
        public function pget_tag(){
            return $this->_tag;
        }
        
        public function pset_tag($tag){
            if (!is_null($tag) && is_string($tag) && mb_trim($tag) != ""){
                if (\mb_strpos($tag, ":")){
                    list($this->_namespace, $this->_tag) = explode(":", $tag);
                }else{
                    $this->_tag = $tag;
                }
            }
        }
        
        public function pget_namespace(){
            return $this->_namespace;
        }
        
        public function pset_namespace($namespace){
            $this->_namespace = $namespace;
        }
        
        public function pget_parent(){
            return $this->_parent;
        }
        
        public function pset_parent($parent){
            $this->_parent = $parent;
        }
        
        public function pget_text(){
            return $this->value();
        }
        
        public function pget_attributes(){
            return $this->_attributes;
        }
        
        /*
         * Dynamic functions
         */
        //public function __get($key){
        //    $output = array();
        //    
        //    $child_count = $this->count();
        //    for($i = 0; $i < $child_count; $i++){
        //        $child = $this->get($i);
        //        if ($child instanceof xml){
        //            if ($child->tag == $key){
        //                $output[] = &$child;
        //            }
        //        }
        //    }
        //    
        //    if (count($output)){
        //        return $output;
        //    }else{
        //        $parent_value = parent::__get($key);
        //        if (is_null($parent_value)){
        //            return $output;
        //        }else{
        //            return $parent_value;
        //        }
        //    }
        //}
        //
        //public function __set($key, $value){
        //    $object = $this->__get($key);
        //    
        //    if (isset($object) && $object instanceof xml){
        //        $object->clear();
        //        return $object->add($value);
        //    }else{
        //        $parent_value = parent::__set($key, $value);
        //        if ($parent_value !== false){
        //            return $object->add(new \application\xml($key, $value));
        //        }else{
        //            return $parent_value;
        //        }
        //    }
        //}
        
        public function __toString(){
            return $this->render();
        }
        
        /*
         * Child functions
         */
        public function find($selector = null){
            /* This function replaces __get() && __set() */
            return new aquery($this, $selector, $this->parent);
        }
        
        public function add(/*$data = null*/){
            //if (isset($data)) $this->_add($data);
            $this->_add(func_get_args());
        }
        
        public final function _add($child, $parse = true){
            if (!is_null($child)){
                if (is_array($child)){
                    foreach($child as $c) $this->_add($c);
                }elseif ($child instanceof xml){
                    $child->parent = $this;
                    $this->_children[] = $child;
                }elseif(is_string($child)){
                    if (self::is_xml($child) && $parse){
                        $this->_add(static::parse($child));
                    }else{
                        $this->_children[] = $child;
                    }
                }else{
                    $this->_children[] = strval($child);
                }
                
                $this->trigger(self::EVENT_CHILD_ADDED, array('child' => $child));
            }
        }
        
        public function get($index = null){
            if (isset($index)){
                if ($index < $this->count()){
                    if (is_string($this->_children[$index])){
                        return $this->process_cdata_tags($this->_children[$index]);
                    }else{
                        return $this->_children[$index];
                    }
                }
            }else{
                $final = [];
                foreach($this->_children as $child){
                    if (is_string($child)){
                        $final[] = $this->process_cdata_tags($child);
                    }else{
                        $final[] = $child;
                    }
                }
                return $final;
            }
        }
        
        public function set($index, $item){
            if (isset($index)){
                if ($index < $this->count()){
                    $this->_children[$index] = $item;
                }
            }
        }
        
        public function remove($index_or_child = null){
            if (is_null($index_or_child)){
                $children = $this->_children;
                $this->_children = array();
                foreach($children as $child){
                    $this->trigger(self::EVENT_CHILD_REMOVED, array('child' => $child));
                }
            }elseif (is_numeric($index_or_child)){
                if ($index_or_child < $this->count()){
                    $child = $this->_children[$index_or_child];
                    $this->_children = array_remove($this->_children, $index_or_child);
                    $this->trigger(self::EVENT_CHILD_REMOVED, array('child' => $child));
                    return true;
                }
            }else{
                for($i = 0; $i < $this->count(); $i++){
                    if ($this->_children[$i] == $index_or_child){
                        $child = $this->_children[$i];
                        $this->_children = array_remove($this->_children, $i);
                        $this->trigger(self::EVENT_CHILD_REMOVED, array('child' => $child));
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        public function clear(){
            $this->remove();
        }
        
        public function count(){
            return count($this->_children);
        }
        
        public function value($value = null){
            if ($value && is_string($value)) $this->add($value);
            
            $output = "";
            $children = $this->get();
            
            foreach($children as $c){
                if (is_string($c)){
                    $output .= $this->process_cdata_tags($c);
                }elseif($c instanceof xml){
                    $output .= $c->value();
                }
            }
            
            return $output;
        }
        
        /*
         * CDATA methods
         */
        public function add_cdata_token($token, $data){
            $this->_cdata_tokens[$token] = $data;
        }
        
        public function process_cdata_tags($string){
            foreach($this->_cdata_tokens as $token => $data){
                $string = preg_replace("/{$token}/", $data, $string);
            }
            return $string;
        }
        
        /*
         * Attribute functions
         */
        public function attr($key, $value = null){
            if (!is_null($value)){
                $this->_attributes[$key] = $value;
            }
            
            if (isset($this->_attributes[$key])){
                return $this->_attributes[$key];
            }
            
            return null;
        }
        
        public function attribute($key, $value = null){
            return $this->attr($key, $value);
        }
        
        public function remove_attr($key){
            if (isset($this->_attributes[$key])){
                unset($this->_attributes[$key]);
            }
        }
        
        public function remove_attribute($key){
            return $this->remove_attr($key);
        }
        
        public function has_attr($key){
            return in_array($key, array_keys($this->_attributes));
        }
        
        public function has_attribute($key){
            return $this->has_attr($key);
        }
        
        /*
         * Escape functions
         */
        public static function escape($string){
            $string = mb_ereg_replace("&", "&amp;", $string, "m");
            $string = mb_ereg_replace("\"", "&quot;", $string, "m");
            $string = mb_ereg_replace("'", "&apos;", $string, "m");
            $string = mb_ereg_replace("\<", "&lt;", $string, "m");
            $string = mb_ereg_replace("\>", "&gt;", $string, "m");
            
            
            return $string;
        }
        
        public static function unescape($string){
            $string = mb_ereg_replace("&amp;", "&", $string, "m");
            $string = mb_ereg_replace("&apos;", "'", $string, "m");
            $string = mb_ereg_replace("&lt;", "<", $string, "m");
            $string = mb_ereg_replace("&gt;", ">", $string, "m");
            $string = mb_ereg_replace("&quot;", "\"", $string, "m");
            
            return $string;
        }
        
        /*
         * Render functions
         */
        public function render_attribute($key, $value){
            return $key . "=\"" . static::escape($value) . "\"";
        }
        
        public function render($close_all_empty_tags = false, $add_slash_to_empty_tags = true, $depth = 0){
            return $this->_render($close_all_empty_tags, $add_slash_to_empty_tags, $depth);
        }
        
        /* This function exists so that children can more easily override the default behaviour */
        public function _render($close_all_empty_tags = false, $add_slash_to_empty_tags = true, $depth = 0){
            $this->trigger(self::EVENT_RENDER);
            $readable = strtolower($this->setting('xml.readable')) == 'yes' ? true : false;
            $tag = $this->_tag;
            
            if (mb_trim($tag) == "" || is_null($tag)) return "";
            
            if (isset($this->_namespace)){
                $tag = $this->_namespace . ":" . $this->_tag;
            }
            
            $xml = "";
            
            if ($readable) for($i = 0; $i < $depth; $i++) $xml .= "  ";
            
            $xml .= "<{$tag}";
            
            foreach($this->_attributes as $key => $value){
                $xml .= " " . $this->render_attribute($key, $value);
            }
            
            if (($this->_closing_tag == false) && (count($this->_children) == 0)){
                if ($close_all_empty_tags){
                    $xml .= "></{$tag}>";
                    if ($readable) $xml .= "\n";
                }elseif ($add_slash_to_empty_tags){
                    $xml .= " />";
                    if ($readable) $xml .= "\n";
                }else{
                    $xml .= ">";
                    if ($readable) $xml .= "\n";
                }
            }else{
                $xml .= ">";
                
                $child_depth = $depth + 1;
                $did_indent = false;
                $last_was_child = false;
                foreach($this->_children as $child){
                    if ($child instanceof xml){
                        
                        if ($readable && !$did_indent){
                            $xml .= "\n";
                            $did_indent = true;
                        }
                        $xml .= $child->render($close_all_empty_tags, $add_slash_to_empty_tags, $child_depth);
                        $last_was_child = true;
                    }elseif (is_string($child)){
                        $string = static::escape($child);
                        foreach($this->_cdata_tokens as $token => $data){
                            $string = preg_replace("/" . static::escape($token) . "/", "<![CDATA[{$data}]]>", $string);
                        }
                        $xml .= $string;
                        $last_was_child = false;
                    }else{
                        try{
                            $xml .= $child;
                            $last_was_child = false;
                        }catch(Exception $e){
                            
                        }
                    }
                }
                if ($readable && $last_was_child) for($i = 0; $i < $depth; $i++) $xml .= "  ";
                $xml .= "</{$tag}>";
                if ($readable) $xml .= "\n";
            }
            
            $this->trigger(self::EVENT_RENDERED, array('output' => $xml));
            return $xml;
        }
        
        /*
         * Parser functions
         */
        public static function parse($data, $return_as_document = false, $alternative_first_node_object = null, $cdata_tokens = []){
            if (is_string($data)){
                /* Convert the data to an array */
                /* Remove xml tag */
                $data = preg_replace("/<\?.*?>\s?/", "", $data);
                
                /* Tokenize the cdata tags */
                $data = static::parse_cdata_tags($data, $cdata_tokens);
                
                /* Replace <.../> with <... /> */
                $data = preg_replace("/([^\s])\/>/", "$1 />", $data);
                
                /* Split the tags */
                $data = preg_split("/</", $data);
                
                /* Remove empty elements */
                $final = array();
                foreach($data as $element){
                    if (!preg_match("/^\s*$/", $element)){
                        $final[] = $element;
                    }
                }
                
                $data = $final;
            }
            
            if (is_array($data) && count($data)){
                $nodes = array();
                
                if (mb_strpos($data[0], ">")){
//                    list($tag_data, $string_data) = mb_split("/>/", $data[0]);
                    list($tag_data, $string_data) = explode(">", $data[0]);
                    $tag_data = mb_trim($tag_data);
                }else{
                    $tag_data = mb_trim($data[0]);
                    $string_data = "";
                }
                //$string_data = trim($string_data);
                
                if ($tag_data){
                    if (isset($alternative_first_node_object) && $alternative_first_node_object instanceof xml){
                            $node = $alternative_first_node_object;
                    }else{
                        if ($return_as_document){
                            $node = new xml_document();
                        }else{
                            $node = new xml();
                        }
                    }
                    $has_children = true;
                    
                    if ($string_data != ""){
                        /* Check if the string contains any cdata tokens */
                        $cdata_token_matches = [];
                        if (preg_match_all("/&cdata[0-9]+;/", $string_data, $cdata_token_matches)){
                            foreach($cdata_token_matches[0] as $token){
                                /* Add the token and it's data to the child node */
                                $node->add_cdata_token($token, $cdata_tokens[$token]);
                            }
                        }
                        $node->add(static::unescape($string_data));
                    }
                    
                    
                    $parts = explode(" ", $tag_data);
//                    $parts = mb_split("/ /", $tag_data);
                    
                    if (count($parts) >= 0){
                        /* Set the tag */
                        $node->tag = $parts[0];
                        
                        /* Parse the attributes */
                        $current_part = "";
                        $name = "";
                        for($i = 1; $i < count($parts); $i++){
                            $parts[$i] = mb_trim($parts[$i]);
                            if ($parts[$i] == "/"){
                                $has_children = false;
                            }elseif ($parts[$i]){
                                if ($current_part == ""){
                                    if (preg_match("/=\"/", $parts[$i])){
                                        list($name, $temp) = explode("=", $parts[$i], 2);
//                                        list($name, $temp) = mb_split("/=/", $parts[$i], 2);
                                        $parts[$i] = $temp;
                                        $name = mb_trim($name);
                                    }
                                }
                                if (preg_match("/\"$/", $parts[$i])){
                                    $current_part .= " " . mb_substr($parts[$i], 0, mb_strlen($parts[$i]) - 1);
                                    $value = mb_trim($current_part);
                                    $current_part = "";
                                    $value = preg_replace("/^\"|'/", "", $value);
                                    $value = preg_replace("/\"|'$/", "", $value);
                                    $node->attr($name, static::unescape($value));
                                }else{
                                    $current_part .= " " . $parts[$i];
                                }
                            }
                        }
                        
                        /* Ok, we are going to process the remainder of the document */
                        $final = array();
                        $children = array();
                        $depth = 0;
                        $complete = !$has_children;
                        $this_tag = $node->namespace ? $node->namespace . ":" . $node->tag : $node->tag;
                        $data_node = null;
                        
                        
                        for($i = 1; $i < count($data); $i++){
                            if (preg_match("/^" . $this_tag ."/", $data[$i])){
                                $depth++;
                                if ($complete){
                                    $final[] = $data[$i];
                                }else{
                                    $children[] = $data[$i];
                                }
                            }elseif(preg_match("/^\/{$this_tag}/", $data[$i])){
                                //print "Here with {$data[$i]} at depth {$depth} on tag {$node->tag_name}\n";
                                //print_r($children);
                                if ($depth == 0){
                                    //Parse the children
                                    $parsed_nodes = static::parse($children, false, null, $cdata_tokens);
                                    
                                    if (is_array($parsed_nodes)){
                                        foreach($parsed_nodes as $n){
                                            if ($n instanceof xml) $n->parent = $node;
                                            $node->add($n);
                                        }
                                        //$node->children = array_merge($node->children, $parsed_nodes);
                                    }else{
                                        if ($parsed_nodes instanceof xml){
                                            $parsed_nodes->parent = $node;
                                            $node->add($parsed_nodes);
                                        }
                                    }
                                    $complete = true;
                                }else{
                                    $depth--;
                                    if ($complete){
                                        $final[] = $data[$i];
                                    }else{
                                        $children[] = $data[$i];
                                    }
                                }
                                
                                list($end_tag, $data_node) = explode(">", $data[$i]);
//                                list($end_tag, $data_node) = mb_split("/>/", $data[$i]);
                                
                                if (mb_strlen(mb_trim($data_node)) == 0){
                                    $data_node = null;
                                }
                            }else{
                                if ($complete){
                                    $final[] = $data[$i];
                                }else{
                                    $children[] = $data[$i];
                                }
                            }
                        }
                        $nodes[] = $node;
                        
                        if($data_node) $nodes[] = $data_node;
                        if (count($final)){
                            $parsed_nodes = static::parse($final, false, null, $cdata_tokens);
                            if (is_array($parsed_nodes)){
                                $nodes = array_merge($nodes, $parsed_nodes);
                            }else{
                                $nodes[] = $parsed_nodes;
                            }
                        }
                        
                        if (count($nodes) == 1){
                            return $nodes[0];
                        }
                        return $nodes;
                    }
                }
            }
        }
        
        public static function parse_cdata_tags($xml_string, array &$cdata_tokens){
            /* Break the string by parts */
            mb_regex_set_options("i");
            $parts = mb_split("<!\[CDATA\[|\]\]>", $xml_string);
            
            /* Reset the string */
            $xml_string = "";
            
            /* Loop thru the parts */
            for ($i = 0; $i < count($parts); $i++){
                /* Get the current part */
                $part = $parts[$i];
                
                if ($i % 2 == 0){
                    $xml_string .= $part;
                }else{
                    $token = "&cdata" . count($cdata_tokens) . ";";
                    $cdata_tokens[$token] = $part;
                    $xml_string .= $token;
                }
                
                
            }
            
            return $xml_string;
        }
        
        /*
         * Helper functions
         */
        public static function is_xml($string){
            $string = mb_trim($string);
            $pattern = "/^(<\?xml .*?>)?\s*<(.*\s*)*>$/";
            
            return preg_match($pattern, $string);
        }
    }

}
