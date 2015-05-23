<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class view extends html{
        
        public function __construct($tag = 'div', $data = null, $attributes = array()){
            parent::__construct($tag, $data, $attributes);
            $this->add_class('view');
            $class_name = array_pop(explode("\\", get_class($this)));
            if (preg_match("/^view_/", $class_name)) $class_name = mb_substr($class_name, 5);
            $class_name = str_replace("_", "-", $class_name);
            $this->add_class($class_name);
            //$this->set_id();
        }
        
    }

}

?>