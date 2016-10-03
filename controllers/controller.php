<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
 *   
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *   
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *   
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     adapt
 * @author      Matt Bruton <matt.bruton@gmail.com>
 * @copyright   2016 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * The foundation controller, the controller that all
     * other controllers inherit from.
     *
     * @property xml|html|view $view
     * The view being managed by this controller.
     * @property model $model
     * The model being used by this controller, if any.
     * @property string $content_type
     * The content type of the view. This is usually handled for you, the only
     * time you need to change this is if you are outputing only a single view
     * and that view isn't html.
     * @property string $url
     * The URL that was passed to this controller during routing.
     * @property string $url_mount_point
     * The point in the URL where this controller is mounted.
     */
    abstract class controller extends base{
        
        /** @ignore */
        protected $_view;
        
        /** @ignore */
        protected $_model;
        
        //protected $_content_type;
        
        /** @ignore */
        protected $_children;
        
        /** @ignore */
        protected $_url;
        
        /** @ignore */
        protected $_url_mount_point;
        
        /**
         * Contstructor
         */
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
        /** @ignore */
        public function pget_view(){
            return $this->_view;
        }
        
        /** @ignore */
        public function pset_view($view){
            $this->_view = $view;
        }
        
        /** @ignore */
        public function pget_model(){
            return $this->_model;
        }
        
        /** @ignore */
        public function pset_model($model){
            $this->_model = $model;
        }
        
        /** @ignore */
        public function pget_content_type(){
            //print "Getting: {$this->_content_type}|||";
            return $this->store('adapt.content_type');
            //return $this->_content_type;
        }
        
        /** @ignore */
        public function pset_content_type($content_type){
            $this->store('adapt.content_type', $content_type);
            //print "Setting: {$content_type}|||";
            //$this->_content_type = $content_type;
        }
        
        /** @ignore */
        public function pget_url(){
            return $this->_url;
        }
        
        /** @ignore */
        public function pset_url($url){
            $this->_url = $url;
        }
        
        /** @ignore */
        public function pget_url_mount_point(){
            return $this->_url_mount_point;
        }
        
        /**
         * Adds a sub view to the controllers view.
         *
         * This is the same as:
         * <code>
         * class some_controller extends \adapt\controller{
         *
         *      public function view_default(){
         *          //Create a view
         *          $view = new html_div();
         *
         *          //Add the view to the controllers view
         *          $this->view->add($view);
         *      }
         *      
         * }
         * </code>
         *
         * @access public
         * @param string|xml|html|view
         * The view to be added
         */
        public function add_view($view){
            $this->view->add($view);
        }
        
        /**
         * Load and return a controller
         *
         * This is the only offically supported way of loading a
         * view controller.  If you load a view controller in the
         * same way as any other object then any actions the controller
         * has will be in-accessable.
         *
         * Lets say we want to mount /test to a view controller named
         * controller_test.  The correct way to do this is:
         * <code>
         * class controller_root extends \adapt\controller{
         *
         *      public function view_test(){
         *          return $this->load_controller('\\some_namespace\\controller_test');
         *      }
         *      
         * }
         * </code>
         *
         * In the next example we've loaded and returned the controller
         * in the same way we would any other object.
         *
         * <code>
         * class controller_root extends \adapt\controller{
         *
         *      public function view_test(){
         *          return new \some_namespace\controller_test();
         *      }
         *      
         * }
         * </code>
         *
         * This may appear to work in some instances, however actions will not.
         * Adapt routes actions and views seperately, when load_controller is called
         * the controller is cached so the same instance can process the actions and output
         * the view.
         *
         * @access public
         * @param string
         * The name of the controller to be loaded. This should include
         * it's full namespace.
         * @param boolean
         * Should the view from the controller we are loading be appended to
         * our view?  Default is true.
         * @return null|false|controller
         * null is the controller wasn't found.
         * false if $name is empty
         * Instance of \adapt\controller if successful
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
        
        
        /**
         * URL Routing
         *
         * When you need more control over URL routing you can over-ride
         * this method.
         *
         * Lets assume we have a view controller mounted on the URL
         * /something
         *
         * If we visit the URL /something/something-else/hello our view controller
         * will receive via the route function the portion of the URL after
         * the mount point. In our case the $url param on our controllers
         * route method will receive something-else/hello.
         *
         * The route method can return:
         * * **null** Causing the DOM to be output to the browser
         * * **string** Causing the string to be output to the browser
         * * **xml|html|view|page** Causing the object to be rendered and sent
         *
         * @access public
         * @param string
         * The URL to route
         * @param boolean
         * Is this URL an action?
         * @return null|string|xml|html|view|page
         * Output to be sent to the client.
         */
        public function route($url, $is_action = false){
            $url = trim($url);
            $this->_url_mount_point = '/' . trim(substr($this->request['url'], 0, strlen($this->request['url']) - strlen($url)), '/');
            
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
            
            //print new html_pre(get_class($this) . ": '{$this->url}'");
            
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
        
        /**
         * Over-rides the DOM and displays the HTTP error.
         *
         * @access public
         * @param integer
         * The HTTP error code
         * @return page
         * Returns the page
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