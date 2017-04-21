<?php

namespace adapt{

    defined('ADAPT_STARTED') or die();
    
    /**
     * Description of view_script
     *
     * @author matt
     */
    class view_style extends view{
        
        public function __construct($script = null, $attributes = array(['type' => 'text/css'])){
            parent::__construct('style', $script, $attributes);
        }
        
        public static function escape($string){
            return $string;
        }
        
        public static function unescape($string){
            return $string;
        }
        
    }
}