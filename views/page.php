<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class page extends html{
        
        protected $_head;
        protected $_body;
        
        public function __construct($title = "", $data = null, $attributes = array()){
            $this->_head = new html_head();
            $this->_body = new html_body();
            
            parent::__construct('html', $data, $attributes);
            parent::add($this->_head, $this->_body);
            
            $this->attr('lang', 'en');
            
            if ($title != ""){
                $this->head->add(new html_title($title));
            }
            
            $this->head->add(new html_meta(array('http-equiv' => 'content-type', 'content' => 'text/html;charset=utf-8')));
        }
        
        public function aget_head(){
            return $this->_head;
        }
        
        public function aget_body(){
            return $this->_body;
        }
        
        public function pget_title(){
            return $this->find('title')->text();
        }
        
        public function pset_title($title){
            if ($this->find('head title')->size() == 0){
                $this->find('head')->prepend(new html_title($title));
            }else{
                $this->find('head title')->text($title);
            }
        }
        
        public function add(){
            $this->body->add(func_get_args());
        }
        
        public function render(){
            $this->body->add(new html_comment('Powered by Adapt Framework - http://www.adaptframework.com'));
            return "<!DOCTYPE html>\n" . parent::render();
        }
        
    }
    
}

?>