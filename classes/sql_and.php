<?php

namespace frameworks\adapt{
    
    class sql_and extends sql{
        
        protected $_conditions = array();
        
        public function __construct(){
            
            foreach(func_get_args() as $arg){
                if (is_array($arg)){
                    foreach($arg as $a){
                        $this->_conditions[] = $a;
                    }
                }else{
                    $this->_conditions[] = $arg;
                }
            }
            
        }
        
        public function aget_conditions(){
            return $this->_conditions;
        }
        
        public function add(){
            foreach(func_get_args() as $arg){
                if (is_array($arg)){
                    foreach($arg as $a){
                        $this->_conditions[] = $a;
                    }
                }else{
                    $this->_conditions[] = $arg;
                }
            }
        }
    }
    
}

?>