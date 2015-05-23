<?php


namespace frameworks\adapt{

    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;

    class aquery extends base{
        
        protected $_root;
        protected $_parent;
        protected $_elements;
        
        
        /* Class functions */
        public function __construct($document = null, $selector = null, $parent = null, $root = null){
            parent::__construct();
            
            $this->_elements = array();
            if (is_null($root)){
                $this->_root = $this;
            }else{
                $this->_root = $root;
            }
            
            $this->_parent = $parent;
            
            if (is_null($document)) $document = $this->dom;
            
            if (!is_array($document)) $document = array($document);
            
            foreach($document as $d){
                if (is_string($d)){
                    $d = xml::parse($d);
                }
                
                if ($d instanceof xml){
                    $this->_elements[] = $d;
                }
            }
            //print_r($this->_elements);
            if ($selector){
                //print_r($selector);
                //print_r($this->_elements);
                $this->_elements = $this->find($selector)->elements;
            }
        }
        
        public function aset_elements($elements){
            $this->_elements = $elements;
        }
        
        public function aget_elements(){
            return $this->_elements;
        }
        
        
        /* CSS functions */
        public function add_class($class_name){ //TESTED
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (!$classes) $classes = "";
                if (is_string($classes) && !preg_match("/\b{$class_name}\b/", $classes)){
                    $e->attribute('class', trim($classes . " " . $class_name));
                }
            }
            
            return $this;
        }
        
        public function css($property, $value = null){ //TESTED
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
        
        public function remove_class($class_name){ //TESTED
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (is_string($classes) && preg_match("/\b{$class_name}\b/", $classes)){
                    $e->attribute('class', preg_replace("/\b{$class_name}\b/", "", $classes));
                    $e->attribute('class', trim($e->attribute('class')));
                }
            }
            
            return $this;
        }
        
        public function has_class($class_name){ //TESTED
            foreach($this->elements as $e){
                $classes = $e->attribute('class');
                if (is_string($classes)) return preg_match("/\b{$class_name}\b/", $classes);
            }
            
            return false;
        }
        
        public function toggle_class($class_name, $switch = null){ //TESTED
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
        
        /* Data functions */
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
        
        /* Attribute functions */
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
        
        public function remove_attr($attribute ){ //TESTED
            $attributes = explode(" ", $attribute);
            foreach($attributes as $a){
                $a = trim($a);
                foreach($this->elements as $e){
                    $e->remove_attribute($a);
                }
            }
            
            return $this;
        }
        
        /* Manipulation */
        public function after($content){ //TESTED
            
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
        
        public function append($content){ //TESTED
            foreach($this->elements as $e){
                if ($e instanceof xml){
                    $e->_add($content);
                }
            }
            
            return $this;
        }
        
        public function before($content){ //TESTED
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
            
            //From the original framework before xml was changed
            //if (!is_array($content)) $content = array($content);
            //foreach($this->elements as $e){
            //    if ($e->parent instanceof xml){
            //        $children = $e->parent->children;
            //        $e->parent->children = array();
            //        foreach($children as $child){
            //            if ($child === $e){
            //                $e->parent->_add($content);
            //            }
            //            $e->parent->_add($child);
            //        }
            //    }
            //}
            //
            //return $this;
        }
        
        public function detach($selector = null){ //TESTED
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
         * Renamed from 'empty' because it
         * is a keyword in php.
         */
        public function clear(){ //TESTED
            foreach($this->elements as $e){
                $e->clear();
            }
            
            return $this;
        }
        
        public function prepend($content){ //TESTED
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
        
        public function remove($selector = null){ //TESTED
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
        
        /* Not supported */
        // public function replace_all($target){
        //     
        // }
        
        public function replace_with($content){ //TESTED
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
        
        public function unwrap(){ //TESTED
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
        
        public function wrap($wrapper){
            if ($wrapper instanceof xml) $wrapper = strval($wrapper->render());
            
            foreach($this->elements as $e){
                $node = xml::parse($wrapper);
                
                if ($e->parent instanceof xml){
                    $children = $e->parent->get();
                    $e->parent->clear();
                    $parent = $e->parent;
                    foreach($children as $c){
                        if ($c === $e){
                            $node->add($e);
                            $parent->add($node);
                        }else{
                            $parent->add($c);
                        }
                    }
                }
            }
            
            return $this;
        }
        
        /* Not supported... yet */
        //public function wrap_all($wrapper){
        //    
        //}
        
        public function wrap_inner($wrapper){ //TESTED
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
        
        /* Misc functions */
        public function get($index = null){ //TESTED
            
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
        
        public function to_array(){ //TESTED
            return $this->_elements;
        }
        
        public function size(){ //TESTED
            return count($this->_elements);
        }
        
        /* Traversing - Filters */
        public function eq($index){ //TESTED
            if (!is_null($index)){
                if ($index >= 0 && $index < count($this->elements)){
                    return new aquery($this->elements[$index], null, $this, $this->_root);
                }elseif($index < 0){
                    $i = count($this->elements) + $index;
                    //print "i = {$i}\n";
                    if ($i >= 0 && $i < count($this->elements)){
                        return new aquery($this->elements[$i], null, $this, $this->_root);
                    }
                }
            }
            
            return $this;
        }
        
        public function filter($selector){ //TESTED
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
        
        public function find($selector){ //TESTED
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
        
        public function first(){ //TESTED
            return $this->eq(0);
        }
        
        public function has($selector){ //TESTED
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
        
        public function is($selector){ //TESTED
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
        
        public function last(){ //TESTED
            return $this->eq(-1);
        }
        
        public function map(){
            //TODO
        }
        
        public function not($selector){ //TESTED
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
            //print "START={$start}\nEND={$end}\n";
            for($i = 0; $i < count($this->elements); $i++){
                if ($i >= $start && $i <= $end){
                    $output[] = $this->elements[$i];
                }
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        /* Traversing - Misc */
        public function add($selector){ //TESTED
            $output = $this->elements;
            
            $q = new aquery($this->_root->elements, $selector, $this, $this->_root);
            $output = array_merge($output, $q->get());
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        public function and_self(){ //TESTED - But I'm not sure if I understand the documentation on jQuery...
            $output = $this->_elements;
            
            if ($this->_parent instanceof aquery){
                $output = array_merge($output, $this->_parent->elements);
            }
            
            return new aquery($output, null, $this, $this->_root);
        }
        
        public function contents(){
            //TODO
        }
        
        public function end(){ //TESTED
            if ($this->_parent instanceof aquery){
                return $this->_parent;
            }
            
            return $this;
        }
        /* Tree Traversal */
        public function children($selector = null){ //TESTED
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
        
        public function closest($selector){ //TESTED
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
        
        public function next_all($selector = null){ //TESTED
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
        
        public function next_until($selector, $filter){ //TODO
            
        }
        
        public function parent($selector = null){ //TESTED
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
        
        public function parents($selector = null){ //TESTED
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
        
        public function parents_until($selector, $filter){ //TODO
            
        }
        
        public function prev($selector = null){ //TESTED
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
        
        public function prev_until($selector, $filter){ //TODO
            
        }
        
        public function siblings($selector = null){ //TESTED
            $prev = $this->prev_all($selector);
            $next = $this->next_all($selector);
            
            return new aquery(array_merge($prev->elements, $next->elements), null, $this, $this->_root);
        }
        
        /* Manipulation */
        public function each($function){
            if (is_callable($function) && is_object($function)){
                for($i = 0; $i < count($this->elements); $i++){
                    $function($i, $this->elements[$i]);
                }
            }
        }
        
        
        /* Protected */
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
                        
                        }
                    }
                    
                    return true;
                }
                
            }
            
            return false;
        }
        
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

?>