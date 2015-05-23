<?php

namespace frameworks\adapt{
    
    class sql_condition extends sql{
        
        protected $_value_1 = "";
        protected $_value_2 = "";
        protected $_condition = "=";
        
        public function __construct($value1, $condition, $value2){
            $this->_value_1 = $value1;
            $this->_value_2 = $value2;
            $this->_condition = $condition;
        }
        
        public function aget_value_1(){
            return $this->_value_1;
        }
        
        public function aget_value_2(){
            return $this->_value_2;
        }
        
        public function aget_condition(){
            return $this->_condition;
        }
        
    }
    
}

?>