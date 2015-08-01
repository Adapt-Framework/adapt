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
    
    class selector extends base{
        
        const ATTRIBUTE_CONTAINS_PREFIX_SELECTOR = "|=";
        const ATTRIBUTE_CONTAINS_SELECTOR = "*=";
        const ATTRIBUTE_CONTAINS_WORD_SELECTOR = "~=";
        const ATTRIBUTE_ENDS_WITH_SELECTOR = "$=";
        const ATTRIBUTE_EQUALS_SELECTOR = "=";
        const ATTRIBUTE_NOT_EQUAL_SELECTOR = "!=";
        const ATTRIBUTE_STARTS_WITH_SELECTOR = "^=";
        const IS_EMPTY = "e=";
        const ALL = "a=";
        const EVEN = "e";
        const ODD = "o";
        
        const RELATIONSHIP_DIRECT = 1;
        const RELATIONSHIP_DISTANT = 2;
        
        public $tag;
        public $attributes;
        public $child;
        public $child_relationship;
        public $parent;
        
        public function __construct(){
            parent::__construct();
            $this->attributes = array();
        }
        
        public static function parse($string_selector){
            $output = array();
            $selectors = array();
            
            /* We may have a comma seperated list of
             * selectors so the first job is to split
             * them.
             */
            
            $selectors = explode(",", $string_selector);
            
            /* Remove any white space */
            foreach($selectors as &$selector){
                $selector = trim($selector);
                $modifiers = array('|=', '*=', '~=', '$=', '!=', '^=', '=');
                foreach($modifiers as $modifier){
                    $parts = explode($modifier, $selector);
                    $selector = "";
                    if (count($parts) > 1){
                        for($i = 0; $i < count($parts); $i++){
                            if ($i > 0) $selector .= $modifier;
                            $selector .= trim($parts[$i]);
                        }
                    }else{
                        $selector .= $parts[0];
                    }
                }
            }
            
            /* We now need to establish if there
             * are any parent > child relationships
             * defined.
             */
            $selector_ptr = null;
            foreach($selectors as &$selector){
                $selector_ptr = new selector();
                $first = true;
                $direct = explode(">", $selector);
                foreach($direct as $part){
                    $is_direct = true;
                    $part = trim($part);
                    if (strpos($part, " ") !== false){
                        $distant = explode(" ", $part);
                        foreach ($distant as $sub_part){
                            if ($first){
                                $selector_ptr = self::parse_selector($sub_part);
                                $output[] = $selector_ptr;
                                $first = false;
                            }else{
                                if ($is_direct){
                                    $selector_ptr->child = self::parse_selector($sub_part);
                                    $selector_ptr->child_relationship = self::RELATIONSHIP_DIRECT;
                                    $selector_ptr->child->parent = $selector_ptr;
                                    
                                    /* Move the pointer */
                                    $selector_ptr = $selector_ptr->child;
                                }else{
                                    $selector_ptr->child = self::parse_selector($sub_part);
                                    $selector_ptr->child_relationship = self::RELATIONSHIP_DISTANT;
                                    $selector_ptr->child->parent = $selector_ptr;
                                    
                                    /* Move the pointer */
                                    $selector_ptr = $selector_ptr->child;
                                }
                            }
                            $is_direct = false;
                        }
                    }else{
                        if ($first){
                            $selector_ptr = self::parse_selector($part);
                            $output[] = $selector_ptr;
                            $first = false;
                        }else{
                            $selector_ptr->child = self::parse_selector($part);
                            $selector_ptr->child_relationship = self::RELATIONSHIP_DIRECT;
                            $selector_ptr->child->parent = $selector_ptr;
                            
                            /* Move the pointer */
                            $selector_ptr = $selector_ptr->child;
                        }
                    }
                }
            }
            return $output;
        }
        
        public static function parse_selector($string_selector){
            //print "IN: {$string_selector}\n";
            
            $s = new selector();
            $string_selector = trim($string_selector);
            
            if ($string_selector == "*"){
                $s->attributes[] = array(
                    'key' => null,
                    'value' => null,
                    'match' => self::ALL
                );
            }elseif (preg_match("/^\[/", $string_selector)){
                /* Just a param set */
                self::parse_param($string_selector, $s);
            }else{
                $pre_data = $string_selector;
                if (strpos($pre_data, "[") !== false){
                    list($pre_data, $params) = explode("[", $string_selector, 2);
                    $params = "[" . $params;
                    self::parse_param($params, $s);
                }
                
                /* Parse the pre data for classes, etc... */
                $matches = array();
                if (preg_match("/^(\w)+/", $pre_data, $matches)){
                    $s->tag = $matches[0];
                }
                
                $matches = array();
                if (preg_match_all("/\.(-|\w)+/", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        $s->attributes[] = array(
                            'key' => 'class',
                            'value' => $class,
                            'match' => self::ATTRIBUTE_CONTAINS_WORD_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/#(-|\w)+/", $pre_data, $matches)){
                    foreach($matches[0] as $id){
                        if (strlen($id) > 1){
                            $id = substr($id, 1);
                        }
                        $s->attributes[] = array(
                            'key' => 'id',
                            'value' => $id,
                            'match' => self::ATTRIBUTE_EQUALS_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:checkbox+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        //if ($s->tag == "") $s->tag = "input";
                        $s->attributes[] = array(
                            'key' => 'type',
                            'value' => $class,
                            'match' => self::ATTRIBUTE_EQUALS_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:checked+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        //if ($s->tag == "") $s->tag = "input";
                        $s->attributes[] = array(
                            'key' => 'checked',
                            'value' => $class,
                            'match' => self::ATTRIBUTE_EQUALS_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:disabled+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        //if ($s->tag == "") $s->tag = "input";
                        $s->attributes[] = array(
                            'key' => 'disabled',
                            'value' => $class,
                            'match' => self::ATTRIBUTE_EQUALS_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:empty+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        
                        $s->attributes[] = array(
                            'key' => null,
                            'value' => $class,
                            'match' => self::IS_EMPTY
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:enabled+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        
                        $s->attributes[] = array(
                            'key' => 'disabled',
                            'value' => 'disabled',
                            'match' => self::ATTRIBUTE_NOT_EQUAL_SELECTOR
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:even+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        
                        $s->attributes[] = array(
                            'key' => null,
                            'value' => null,
                            'match' => self::EVEN
                        );
                    }
                }
                
                $matches = array();
                if (preg_match_all("/\:odd+/i", $pre_data, $matches)){
                    foreach($matches[0] as $class){
                        if (strlen($class) > 1){
                            $class = substr($class, 1);
                        }
                        
                        $s->attributes[] = array(
                            'key' => null,
                            'value' => null,
                            'match' => self::ODD
                        );
                    }
                }
                
            }
            return $s;
        }
        
        public static function parse_param($string_param, &$selector){
            $string_param = trim($string_param);
            $params = explode("[", $string_param);
            
            foreach($params as $pair){
                $pair = preg_replace("/\]$/", "", $pair);
                
                $opertators = array(
                    self::ATTRIBUTE_CONTAINS_PREFIX_SELECTOR,
                    self::ATTRIBUTE_CONTAINS_SELECTOR,
                    self::ATTRIBUTE_CONTAINS_WORD_SELECTOR,
                    self::ATTRIBUTE_ENDS_WITH_SELECTOR,
                    self::ATTRIBUTE_NOT_EQUAL_SELECTOR,
                    self::ATTRIBUTE_STARTS_WITH_SELECTOR,
                    self::ATTRIBUTE_EQUALS_SELECTOR
                );
                
                for($i = 0; $i < count($opertators); $i++){
                    if (strpos($pair, $opertators[$i]) !== false){
                        list($key, $value) = explode($opertators[$i], $pair, 2);
                        $value = preg_replace("/^\"|'/", "", $value);
                        $value = preg_replace("/\"|'$/", "", $value);
                        $selector->attributes[] = array(
                            'key' => $key,
                            'value' => $value,
                            'match' => $opertators[$i]
                        );
                        break;
                    }
                }
            }
        }
        
    }

}
    
?>