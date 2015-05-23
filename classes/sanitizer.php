<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class sanitizer extends base{
        
        public function __construct(){
            parent::__construct();
            
            $store = $this->store('adapt.sanitizer.data');
            if (is_null($store) || !is_array($store)){
                $store = array();
                $this->store('adapt.sanitizer.data', array(
                    'unformatters' => array(),
                    'validators' => array(),
                    'formatters' => array()
                ));
                
            }
            
        }
        
        public function format($key, $value){
            $store = $this->store('adapt.sanitizer.data');
            
            if (isset($store) && isset($store['formatters']) && isset($store['formatters'][$key])){
                if (is_callable($store['formatters'][$key]['php_function'])){
                    $func = $store['formatters'][$key]['php_function'];
                    $value = $func($value);
                }elseif(is_string($store['formatters'][$key]['pattern']) && $store['formatters'][$key]['pattern']){
                    $value = preg_replace("/{$store['formatters'][$key]['pattern']}/", "", $value);
                }
            }
            
            return $value;
        }
        
        public function unformat($key, $value){
            $store = $this->store('adapt.sanitizer.data');
            
            if (isset($store) && isset($store['unformatters']) && isset($store['unformatters'][$key])){
                if (is_callable($store['unformatters'][$key]['php_function'])){
                    $func = $store['unformatters'][$key]['php_function'];
                    $value = $func($value);
                }elseif(is_string($store['unformatters'][$key]['pattern']) && $store['unformatters'][$key]['pattern']){
                    $value = preg_replace("/{$store['unformatters'][$key]['pattern']}/", "", $value);
                }
            }
            
            return $value;
        }
        
        public function validate($key, $value){
            $store = $this->store('adapt.sanitizer.data');
            
            if (isset($store) && isset($store['validators']) && isset($store['validators'][$key])){
                if (is_callable($store['validators'][$key]['php_function'])){
                    $func = $store['validators'][$key]['php_function'];
                    return $func($value);
                }elseif(is_string($store['validators'][$key]['pattern'])){
                    return preg_match("/{$store['validators'][$key]['pattern']}/", $value);
                }
            }
            
            return false;
        }
        
        public function add_format($key, $pattern_or_function, $js_function = null){
            $store = $this->store('adapt.sanitizer.data');
            $formatter = array(
                'pattern' => null,
                'php_function' => null,
                'js_function' => null
            );
            
            if (is_string($pattern_or_function)){
                $formatter['pattern'] = $pattern_or_function;
            }elseif(is_callable($pattern_or_function)){
                $formatter['php_function'] = $pattern_or_function;
            }
            
            $formatter['js_function'] = $js_function;
            
            $store['formatters'][$key] = $formatter;
            $this->store('adapt.sanitizer.data', $store);
        }
        
        public function add_unformat($key, $format, $js_function = null){
            $store = $this->store('adapt.sanitizer.data');
            $cleaner = array(
                'pattern' => null,
                'php_function' => null,
                'js_function' => null
            );
            
            if (is_string($format)){
                $unformatter['pattern'] = $format;
            }elseif(is_callable($format)){
                $unformatter['php_function'] = $format;
            }
            
            $unformatter['js_function'] = $js_function;
            $store['unformatters'][$key] = $unformatter;
            
            $this->store('adapt.sanitizer.data', $store);
        }
        
        public function add_validator($key, $pattern_or_function, $js_function = null){
            $store = $this->store('adapt.sanitizer.data');
            $validator = array(
                'pattern' => null,
                'php_function' => null,
                'js_function' => null
            );
            
            if (is_string($pattern_or_function)){
                $validator['pattern'] = $pattern_or_function;
            }elseif(is_callable($pattern_or_function)){
                $validator['php_function'] = $pattern_or_function;
            }
            
            $validator['js_function'] = $js_function;
            
            $store['validators'][$key] = $validator;
            
            $this->store('adapt.sanitizer.data', $store);
        }
        
        
        
    }
    
    
}

?>