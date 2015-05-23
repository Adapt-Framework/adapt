<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class html_comment extends html{
        
        protected $_comment;
        protected $_type;
        
        const STANDARD = 0;
        const IE_START = 1;
        const IE_END = 2;
        
        public function __construct($comment, $type = 0){
            parent::__construct("_comment_");
            $this->_comment = $comment;
            $this->_type = $type;
        }
        
        public function aget_type(){
            return $this->_type;
        }
        
        public function aget_comment(){
            return $this->_comment;
        }
        
        public function render(){
            $comment = "<!";
            if ($this->type == 0 || $this->type == 1){
                $comment .= "-- ";
            }
            $comment .= $this->_comment;
            if ($this->type == 0 || $this->type == 2){
                $comment .= " --";
            }
            
            $comment .= ">";
            return $comment;
        }
    }
    
}


?>