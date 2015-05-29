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
        
        public function render($not_req_1 = null, $not_req_2 = null, $depth = 0){
            /*
             * We can ignore the first two input params because
             * these are here only for compatibility with xml
             */
            $readable = $this->setting('xml.readable');
            
            $comment = "";
            
            if ($readable) for($i = 0; $i < $depth; $i++) $comment .= "  ";
            
            $comment .= "<!";
            if ($this->type == 0 || $this->type == 1){
                $comment .= "-- ";
            }
            $comment .= $this->_comment;
            if ($this->type == 0 || $this->type == 2){
                $comment .= " --";
            }
            
            $comment .= ">";
            if ($readable) $comment .= "\n";
            return $comment;
        }
    }
    
}


?>