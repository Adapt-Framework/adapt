<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    abstract class controller extends base{
        
        protected $_view;
        protected $_model;
        //protected $_content_type;
        protected $_children;
        protected $_url;
        
        public function __construct(){
            parent::__construct();
            
            $namespaces = explode("\\", get_class($this));
            $class_name = array_pop($namespaces);
            
            $class_name = trim(str_replace("controller", "", $class_name), "_");
            $this->view = new html_div(null, array('class' => "controller {$class_name}"));
            $this->view->set_id();
            
            $this->content_type = "text/html; charset=utf8";
            
            $this->_children = array();
            
            if (isset($this->_model) && is_object($this->_model) && $this->_model instanceof model){
                if (!$this->_model->is_loaded){
                    //TODO: Test
                    if (isset($this->request[$this->_model->table_name])){
                        /* Get the values */
                        $values = $this->request[$this->_model->table_name];
                        
                        /* Get the primary keys */
                        $keys = $this->_model->data_source->get_primary_keys($this->_model->table_name);
                        $ids = array();
                        
                        foreach($keys as $key){
                            if (isset($values[$key]) && !is_null($values[$key]) && $values[$key] != ""){
                                $ids[] = $values[$key];
                            }
                        }
                        
                        if (count($ids)){
                            $this->_model->load($ids);
                        }
                    }
                }
            }
        }
        
        /*
         * Properties
         */
        
        public function aget_view(){
            return $this->_view;
        }
        
        public function aset_view($view){
            $this->_view = $view;
        }
        
        public function aget_model(){
            return $this->_model;
        }
        
        public function aset_model($model){
            $this->_model = $model;
        }
        
        public function aget_content_type(){
            //print "Getting: {$this->_content_type}|||";
            return $this->store('adapt.content_type');
            //return $this->_content_type;
        }
        
        public function aset_content_type($content_type){
            $this->store('adapt.content_type', $content_type);
            //print "Setting: {$content_type}|||";
            //$this->_content_type = $content_type;
        }
        
        public function aget_url(){
            return $this->_url;
        }
        
        public function aset_url($url){
            $this->_url = $url;
        }
        
        /*
         * Adding sub views
         */
        public function add_view($view){
            $this->view->add($view);
        }
        
        /*
         * Controller functions
         */
        public function load_controller($name, $append_view = true){
            $parts = explode("\\", $name);
            if (count($parts) == 0){
                return false;
            }elseif (count($parts) == 1){
                $parts = array('application', $parts[0]);
            }
            if ($parts[0] == ''){
                $parts = array_remove($parts, 0);
            }
            
            $name = "\\" . implode("\\", $parts);
            
            foreach($this->_children as $child){
                if (is_object($child) && $child instanceof $name){
                    return $child;
                }
            }
            
            $controller = self::create_object($name);
            
            if ($controller instanceof $name){
                $this->_children[] = $controller;
                
                if ($append_view == true){
                    $this->add_view($controller->view);
                }
            }
            
            return $controller;
        }
        
        
        /*
         * Routing functions
         */
        public function route($url, $is_action = false){
            $url = trim($url);
            
            if (!isset($url) || $url == ""){
                $url = "default";
            }
            
            $url_components = array_reverse(explode("/", $url));
            $method = array_pop($url_components);
            $method = str_replace("-", "_", $method);
            $url = implode("/", array_reverse($url_components));
            
            if (!$is_action){
                $this->url = $url;
            }
            
            if (strlen($method) > 0){
                if ($is_action){
                    if (count($url_components) == 0){
                        $method = "action_{$method}";
                    }else{
                        $method = "view_{$method}";
                    }
                }else{
                    $method = "view_{$method}";
                }
                
                $permission = "permission_{$method}";
                
                if (isset($method)){
                    
                    //$this->$method();
                    if (method_exists($this, $method) || is_callable(array($this, $method))){
                        if (/*(!method_exists($this, $permission) && !is_callable(array($this, $permission)))*/ is_null($this->$permission()) || $this->$permission() == true){
                            $output =  $this->$method();
                            
                            if ($output instanceof controller){
                                return $output->route($url, $is_action);
                            }else{
                                return $output;
                            }
                        }else{
                            return $this->error(403);
                        }
                    }else{
                        if ($is_action){
                            return $this->error(400);
                        }else{
                            return $this->error(404);
                        }
                    }
                }
                
            }
            
        }
        
        /*
         * Error functions
         */
        public function error($error_code){
            $page = new page();
            
            switch($error_code){
            case 400:
                header("HTTP/1.0 400 Bad Request");
                $page->title = "Error 400: Bad request";
                $page->add(new html_h1("Error 400: Bad request"));
                $page->add(new html_p("Something went wrong and we were unable to process your request. Please try again."));
                break;
            case 403:
                header("HTTP/1.0 403 Forbidden");
                $page->title = "Error 403: Forbidden";
                $page->add(new html_h1("Error 403: Forbidden"));
                $page->add(new html_p("You are not authorised to access this resource."));
                break;
            case 404:
                header("HTTP/1.0 404 Not Found");
                $page->title = "Error 404: Page not found";
                $page->add(new html_h1("Error 404: Page not found"));
                $page->add(new html_p("Oh no! We were unable to find the page you are looking for, it may have got lost or you may have mis-typed the URL."));
                break;
            case 405:
                header("HTTP/1.0 405 Method Not Allowed");
                $page->title = "Error 405: Method not allowed";
                $page->add(new html_h1("Error 405: Method not allowed"));
                $page->add(new html_p("This service does not support this type of request."));
                break;
            case 418:
                header("HTTP/1.0 418 I'm a teapot");
                $page->title = "Error 418: I'm a teapot";
                $page->add(new html_h1("Error 418: I'm a teapot"));
                $page->add(new html_p("Unfortunatly we were unable to process your request because the server thinks it's a teapot."));
                break;
            case 500:
                header("HTTP/1.0 500 Internal Server Error");
                $page->title = "Error 500: Internal server error";
                $page->add(new html_h1("Error 500: Internal server error"));
                $page->add(new html_p("An error occured while processing your request, please try again."));
                break;
            }
            
            return $page;
        }
        
    }

}

?>