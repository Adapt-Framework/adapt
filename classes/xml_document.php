<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class xml_document extends xml{
        
        public function __construct($tag, $data = null, $attributes = array(), $closing_tag = false){
            parent::__construct($tag, $data, $attributes, $closing_tag);
        }
        
        public function render(){
            $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $output .= parent::render();
            return $output;
        }
    }
}

?>