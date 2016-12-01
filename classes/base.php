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
 *
 */

namespace adapt{
    
    /**
     * Prevent direct access
     */
    defined('ADAPT_STARTED') or die;
    
    /**
     * The foundation class for the entire framework.
     *
     * This class provides many of the core framework features.
     * Many properties of this class are shared between all
     * instances of this class, allowing easy data sharing
     * as well as keeping resources to a minimum.
     *
     * @property-read integer $instance_id
     * A unique instance ID
     * @property string|xml|html|view|page $dom
     * Shared property allowing access to the DOM.
     * @property data_source $data_source
     * Shared property allow access to the data source.
     * @property storage_file_system $file_store
     * Shared property allowing access to file storage.
     * @property cache $cache
     * Shared property providing access to the cache
     * @property bundles $bundles
     * Shared property giving access to the bundle management system.
     * @property-read array $request
     * Shared property, equivilant to $_REQUEST
     * @property-read array $response
     * Shared property giving access to the response.  Think of this as the opposite
     * to $_REQUEST.
     * @property sanitizer $sanitize
     * Shared property giving access to the sanitizer.
     */
    class base{
        
        /**
         *  Triggered when an error occurs.
         */
        const EVENT_ERROR = 'adapt.error';
        
        /**
         *  Triggered when the framework is fully
         *  available.
         */
        const EVENT_READY = 'adapt.ready';
        
        /**
         * A unique instance id identifing this class
         * within Adapt framework.
         *
         * This property is read-only and
         * can be accessed via:
         * <code>
         * $id = $this->instance_id;
         * </code>
         *
         * @var integer
         * @access protected
         */
        protected $_instance_id;
        
        /**
         * An array of all errors that have occured during the
         * life of this object, the list is reset whenever the
         * method error is called with a single parameter set
         * to true.
         *
         * @var array
         * @access protected
         */
        protected $_errors;
        
        /**
         * Stores any event handlers
         *
         * @access protected
         * @var array
         */
        protected $_event_handlers;
        
        /**
         * Constructor
         */
        public function __construct(){
            if (!isset($GLOBALS['__adapt'])){
                $GLOBALS['__adapt'] = array();
            }
            
            if (!isset($GLOBALS['__adapt']['instance_counter'])){
                $GLOBALS['__adapt']['instance_counter'] = 0;
            }
            
            $this->_instance_id = $GLOBALS['__adapt']['instance_counter'];
            $GLOBALS['__adapt']['instance_counter']++;
            
            $this->_errors = array();
            $this->_event_handlers = array();
        }
        
        /**
         * Treated as a property, returns the instance ID.
         * 
         * <code>
         * $object = new \adapt\base();
         * $id = $object->instance_id;
         * </code>
         *
         * @access public
         * @return integer
         */
        public function pget_instance_id(){
            return $this->_instance_id;
        }
        
        /**
         * Enable dynamic getting
         *
         * Enables classes to define functions to act
         * as properties.  To create a getter you simply
         * define a function prefixed with 'pget_'.
         *
         * To create a propery on 'test_class' with the name
         * 'foobar' you simply do the following:
         * 
         * <code>
         * class test_class extends \adapt\base{
         *
         *      public function pget_foobar(){
         *          return "Hello world";
         *      }
         *      
         * }
         * </code> 
         */
        public function __get($key){
            /* Dynamic properties */
            $keys = array(
                "dget_{$key}", //Depricated
                "aget_{$key}", //Depricated
                "pget_{$key}", //Property
                "mget_{$key}"  //Model property
            );
            foreach($keys as $k){
                if (method_exists($this, $k)){
                    return $this->$k();
                }
            }
            
            /* Has this property been added via extend()? */
            $extensions = $this->store('adapt.extensions');
            
            if (isset($extensions) && is_array($extensions)){
                $classes = array_keys($extensions);
                foreach($classes as $class){
                    $this_class = get_class($this);
                    if ($this instanceof $class){
                        foreach($keys as $k){
                            if (isset($extensions[$class][$k])){
                                $args = array($this);
                                return call_user_func_array($extensions[$class][$k], $args);
                                
                            }
                        }
                    }
                }
            }
            return null;
        }
        
        /**
         * Enable dynamic setting
         *
         * Enables classes to define functions to act
         * as properties.  To create a setter you simply
         * define a function prefixed with 'pset_'.
         *
         * To create a propery on 'test_class' with the name
         * 'foobar' you simply do the following:
         * 
         * <code>
         * class test_class extends \adapt\base{
         *
         *      public function pset_foobar($value){
         *          
         *      }
         *      
         * }
         * </code> 
         */
        public function __set($key, $value){
            /* Dynamic properties */
            $keys = array(
                "dset_{$key}", //Depricated
                "aset_{$key}", //Depricated
                "pset_{$key}", //Property set
                "mset_{$key}"  //Model property set (Must return true)
            );
            foreach($keys as $k){
                if (method_exists($this, $k)){
                    return $this->$k($value);
                }
            }
            
            /* Has this property been added via extend()? */
            $extensions = $this->store('adapt.extensions');
            
            if (isset($extensions) && is_array($extensions)){
                $classes = array_keys($extensions);
                foreach($classes as $class){
                    $this_class = get_class($this);
                    if ($this instanceof $class){
                        foreach($keys as $k){
                            if (isset($extensions[$class][$k])){
                                $args = array($this, $value);
                                return call_user_func_array($extensions[$class][$k], $args);
                                
                            }
                        }
                    }
                }
            }
            
            return false;
        }
        
        /**
         * Enable dynamic extention of the class at runtime.
         *
         * Say we have a simple class defined in a bundle test_space:
         * 
         * <code>
         * namespace test_space;
         * 
         * class test_class extends \adapt\base{
         *
         *      public function pget_foobar(){
         *          return "Hello world";
         *      }
         *      
         * }
         * </code>
         *
         * And we want to extend this class from another bundle,
         * we can use \adapt\base::extend to extend the class.
         *
         * So lets add a new method extend_test to our test_class.
         *
         * <code>
         * namespace another_bundle;
         *
         * \test_space\test_class::extend('extend_test', function($_this){ return "Hi"; });
         *
         * $test_class = new \test_space\test_class();
         *
         * print $test_class->extend_test();
         * </code>
         */
        public function __call($name, $args){
            //print new html_pre("Calling: {$name}");
            /* Check extensions */
            $extensions = $this->store('adapt.extensions');
            
            if (isset($extensions) && is_array($extensions)){
                $classes = array_keys($extensions);
                foreach($classes as $class){
                    if ($this instanceof $class){
                        if (isset($extensions[$class][$name])){
                            if (is_array($args)){
                                $args = array_merge(array($this), $args);
                            }else{
                                $args = array($this);
                            }
                            return call_user_func_array($extensions[$class][$name], $args);
                        }
                    }
                }
            }
            return null;
        }
        
        /**
         * Create a new class handler.
         *
         * The html class is a class handler, heres how it
         * was defined and how it works.
         *
         * Created as simply as:
         *
         * <code>
         * namespace adapt;
         *
         * class html extends base {
         *      
         * }
         *
         * $adapt = new base();
         * $adapt->add_handler("\\adapt\\html");
         * </code>
         *
         * All classes prefixed with html_ will handled by
         * the html class.
         * <code>
         * $p = new html_p();
         * //Internally is converted to:
         * $p = new html('p');
         * </code>
         *
         * @access public
         * @param string
         * The full namespace and classname of the class to be used
         * as a class handler
         * @return boolean Returns true if the class exists, false if it doesn't.
         */
        public function add_handler($namespace_and_class_name){
            if (class_exists($namespace_and_class_name)){
                $handlers = $this->store('adapt.handlers');
                if (is_null($handlers)) $handlers = array();
                
                $handlers[] = $namespace_and_class_name;
                $this->store('adapt.handlers', $handlers);
                
                return true;
            }
            
            return false;
        }
        
        /**
         * Registers an error and triggers the event \adapt\base::EVENT_ERROR
         *
         * <code>
         * namespace adapt;
         *
         * class some_class extends base{
         *
         *      public function do_not_call(){
         *          $this->error("You called the do not call");
         *      }
         * }
         * </code>
         *
         * @access public
         * @param string
         * An informative string describing the error that has occured.
         */
        public function error($error){
            if (isset($error)){
                if (is_array($error)){
                    foreach($error as $e) $this->error($e);
                } elseif (is_string($error)) {
                    $this->_errors[] = $error;
                    $this->trigger(self::EVENT_ERROR, array('error' => $error));
                }
            }
        }
        
        /**
         * Returns a list of errors that have occured until now.
         *
         * @access public
         * @param boolean
         * When true the errors are cleared.
         * @return array
         * Returns an array of string messages
         */
        public function errors($clear = false){
            $errors = $this->_errors;
            if ($clear) $this->_errors = array();
            return $errors;
        }
        
        
        /**
         * Add an event handler
         *
         * <code>
         * $adapt = new base();
         *
         * $adapt->on(
         *      base::EVENT_READY,
         *      function(){
         *        
         *      }
         * );
         * </code>
         *
         * @access public
         * @param string
         * The event to listen for.
         * @param function
         * A function to call when the event is triggered.
         * @para mixed
         * Data to pass into the called function.
         */
        public function on($event_type, $function, $data = null){
            if (!isset($this->_event_handlers[$event_type])){
                $this->_event_handlers[$event_type] = array();
            }
            
            if (is_callable($function)){
                $this->_event_handlers[$event_type][] = array(
                    'function' => $function,
                    'data' => $data
                );
            }
        }
        
        /**
         * Listens for events in the global space
         * 
         * 
         */
        public static function listen($event_type, $function, $data = null){
            $adapt = $GLOBALS['adapt'];
            $event_handlers = $adapt->store('adapt.global_event_handlers');
            $class_name = get_called_class();
            
            if (!is_array($event_handlers)) $event_handlers = [];
            if (!is_array($event_handlers[$class_name])) $event_handlers[$class_name] = [];
            if (!is_array($event_handlers[$class_name][$event_type])) $event_handlers[$class_name][$event_type] = [];
            
            if (is_callable($function)){
                $event_handlers[$class_name][$event_type][] = [
                    'function' => $function,
                    'data' => $data
                ];
            }
            
            $adapt->store('adapt.global_event_handlers', $event_handlers);
        }
        
        /**
         * Triggers an event
         *
         * @access public
         * @param string
         * The type of event to be triggered
         * @param array
         * Data to be past to any listeners.
         */
        public function trigger($event_type, $event_data = array()){
            // Trigger global events
            $event_handlers = $this->store('adapt.global_event_handlers');
            $class_name = get_class($this);
            
            $classes = array_keys($event_handlers);
            
            foreach($classes as $class){
                if ($this instanceof $class){
                    print '<pre>' . print_r(array_keys($event_handlers[$class]), true) . $event_type . '</pre>';
                    if (isset($event_handlers[$class][$event_type])){
                        foreach($event_handlers[$class][$event_type] as $handler){
                            $data = array(
                                'event_type' => $event_type,
                                'event_data' => $event_data,
                                'object' => $this,
                                'data' => $handler['data']
                            );
                            $function = $handler['function'];
                            call_user_func($function, $data);
                            //call_user_func_array($function, $data);
                            //$function($data);
                        }
                    }
                }
            }
            
            
//            if (isset($event_handlers[$class_name][$event_type]) && is_array($event_handlers[$class_name][$event_type])){
//                foreach($event_handlers[$class_name][$event_type] as $handler){
//                    $data = array(
//                        'event_type' => $event_type,
//                        'event_data' => $event_data,
//                        'object' => $this,
//                        'data' => $handler['data']
//                    );
//                    
//                    $function = $handler['function'];
//                    $function($data);
//                }
//            }
//            
            // Trigger local events
            if (isset($this->_event_handlers[$event_type]) && is_array($this->_event_handlers[$event_type])){
                foreach($this->_event_handlers[$event_type] as $handler){
                    $data = array(
                        'event_type' => $event_type,
                        'event_data' => $event_data,
                        'object' => $this,
                        'data' => $handler['data']
                    );
                    
                    $function = $handler['function'];
                    $continue_event = $function($data);
                    
                    if ($continue_event === false){
                        //Kill the event here
                        return;
                    }
                }
            }
        }
        
        
        /**
         * Temporarily store data against a key or retrieve information stored against a key.
         *
         * The stored data is available via any class extending \adapt\base.
         *
         * <code>
         * //Create a class
         * $basic_class = new \adapt\base();
         *
         * //Store some data
         * $basic_class->store('foo', 'bar');
         *
         * //Create a html p tag
         * $p = new html_p();
         *
         * //Retrieve the data stored against our key 'foo';
         * print $p->store('foo');
         * </code>
         *
         * @access public
         * @param string
         * A unique key used to get or set the data.
         * @param mixed
         * Optional.  When specified data is stored, when absent data is retrived.
         * @return mixed
         * When data is specified return null, else returns the value for
         * key. Please note this value will be null if the key doesn't exist.
         */
        public function store($key, $value = null){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['storage'])) $GLOBALS['__adapt']['storage'] = array();
            
            if (is_null($value)){
                if (isset($GLOBALS['__adapt']['storage'][$key])){
                    return $GLOBALS['__adapt']['storage'][$key];
                }
                
                return null;
            }else{
                $GLOBALS['__adapt']['storage'][$key] = $value;
            }
        }
        
        /**
         * Removes the key and associated data set with self::store()
         *
         * @access public
         * @param string
         * The key to remove.
         */
        public function remove_store($key){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['storage'])) $GLOBALS['__adapt']['storage'] = array();
            
            if (isset($GLOBALS['__adapt']['storage'][$key])){
                unset($GLOBALS['__adapt']['storage'][$key]);
            }
        }
        
        /**
         * Sets a setting or returns the value of a setting.
         * When $value is null the current value of the setting is returned,
         * when $value is present the setting is set.
         *
         * @access public
         * @param string
         * A string representing the name of the setting to set or get.
         * @param mixed
         * Optional.  When provided sets the setting, when missing the value of the setting
         * is returned.
         * @returns mixed
         */
        public function setting($key, $value = null){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['settings'])) $GLOBALS['__adapt']['settings'] = array();
            
            if (is_null($value)){
                if (isset($GLOBALS['__adapt']['settings'][$key])){
                    return $GLOBALS['__adapt']['settings'][$key];
                }
                
                return null;
            }else{
                $GLOBALS['__adapt']['settings'][$key] = $value;
            }
        }
        
        /**
         * Returns an array of all settings currently in effect.
         *
         * @access public
         * @returns array
         */
        public function get_settings(){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['settings'])) $GLOBALS['__adapt']['settings'] = array();
            
            return $GLOBALS['__adapt']['settings'];
        }
        
        /**
         * Replaces all settings currently in effect.
         *
         * @access public
         * @param array
         * A hash array containing key/value pairs to replace the current
         * settings.
         */
        public function set_settings($hash){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            $GLOBALS['__adapt']['settings'] = $hash;
        }
        
        /**
         * Removes a setting.
         *
         * @access public
         * @param string
         * The setting key to be removed.
         */
        public function remove_setting($key){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['settings'])) $GLOBALS['__adapt']['settings'] = array();
            
            if (isset($GLOBALS['__adapt']['settings'][$key])){
                unset($GLOBALS['__adapt']['settings'][$key]);
            }
        }
        
        /**
         * Property representing the current Document Object Model.
         *
         * This property is shared between all classes inheriting from
         * \adapt\base.
         *
         * By default the DOM is an instance of \adapt\page.
         *
         * To access this property you can do the following:
         *
         * <code>
         * $object = new \adapt\base();
         * $dom = $object->dom;
         * </code>
         *
         * @access public
         * @return mixed
         * Returns the current document object model.
         */
        public function pget_dom(){
            return $this->store('adapt.root_view');
        }
        
        /**
         * Set the current document object model
         *
         * This property is shared between all classes inheriting
         * from \adapt\base.
         *
         * To set the DOM:
         * <code>
         * $adapt = new \adapt\base();
         * $dom = new html_html(new html_body(new html_p("I'm the DOM")));
         *
         * $adapt->dom = $dom;
         * </code>
         *
         * @access public
         * @param mixed
         * Typically this will be an instance of \adapt\html or \adapt\xml but can be anything that
         * is printable.
         */
        public function pset_dom($view){
            $this->store('adapt.root_view', $view);
        }
        
        
        /**
         * Shared property containing the current data source object.
         *
         * This property is shared between all instances of \adapt\base.
         *
         * @access public
         * @return data_source
         * Returns an object that conforms to \adapt\interface\data_source, often this
         * object will conform to \adapt\interfaces\data_source_sql
         */
        public function pget_data_source(){
            return $this->store('adapt.data_source');
        }
        
        /**
         * Sets the default data_source.
         *
         * <code>
         * $data_source = new \adapt\data_source_mysql('...');
         *
         * $adapt = new \adapt\base();
         * $adapt->data_source = $data_source;
         * </code>
         *
         * @access public
         * @param data_source
         * The data_source to set.
         */
        public function pset_data_source($data_source){
            $this->store('adapt.data_source', $data_source);
        }
        
        /**
         * Returns the current file store object.
         * By default this is set to an instance of \adapt\storage_file_system.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @return mixed
         */
        public function pget_file_store(){
            return $this->store('adapt.file_store');
        }
        
        /**
         * Set the current file store.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * $param mixed
         * Usually an instance of \adapt\storage_file_system or an object that conforms to this.
         */
        public function pset_file_store($store){
            $this->store('adapt.file_store', $store);
        }
        
        /**
         * Returns the current cache object.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @return cache
         */
        public function pget_cache(){
            return $this->store('adapt.cache');
        }
        
        /**
         * Sets the current cache object.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @param cache
         * Must conform to \adapt\cache
         */
        public function pset_cache($cache){
            $this->store('adapt.cache', $cache);
        }
        
        /**
         * Returns an instance of \adapt\bundles.
         * The bundles object is used to manange bundles within Adapt. This
         * object is shared between all instances of \adapt\base.
         *
         * @access public
         * @return bundles
         * Returns an instance of \adapt\bundles
         */
        public function pget_bundles(){
            return $this->store('adapt._bundles');
        }
        
        /**
         * Sets the bundles property.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @param bundles
         * The bundle object to share.
         */
        public function pset_bundles($bundles){
            $this->store('adapt._bundles', $bundles);
        }
        
        
        
        /**
         * Redirects a page after all actions have been processed.
         *
         * @access public
         * @param string
         * The URL to redirect to.
         * @param boolean
         * Should the response from any processed actions be passed on to the
         * URL we are redirecting to?  This should be false if we are redirecting
         * to a different domain else we may leak data.
         * @param boolean
         * Should we wait until all the actions are processed.  When true the redirect
         * occurs right away and no further actions will be processed.
         */
        public function redirect($url, $pass_on_response = true, $no_wait = false){
            if ($pass_on_response){
                $response = $this->store('adapt.response');
                if (is_array($response)){
                    $response['request'] = $this->request;
                    $url = $url . '?adapt_response=' . urlencode(json_encode($response));
                }
            }
            
            /* Do we have any actions pending?
             * If we do then we are going to store
             * the url against adapt.redirect and
             * the boot process will then handle
             * on our behalf once the remaining
             * actions are processed.
             */
            
            if (is_null($this->store('adapt.next_action')) || $no_wait){
                /* Redirect now */
                header("location: {$url}");
                exit(0);
            }else{
                $this->store('adapt.redirect', $url);
            }
        }
        
        /**
         * Request property, this is equivalent to $_REQUEST but is
         * the preferred method for dealing with server input.
         *
         * This property is shared between all instances of \adapt\base.
         *
         * @access public
         * @property array $request
         * @return array
         */
        public function pget_request(){
            $request = $this->store('adapt.request');
            if (is_null($request)){
                $request = &$_REQUEST;
                $this->store('adapt.request', $request);
            }
            
            return $request;
        }
        
        /**
         * Returns or sets a value in the request array.
         *
         * This is a replacement for $_REQUEST, this method
         * shares data between all instances of \adapt\base
         *
         * @access public
         * @param string
         * The key to set or get.
         * @param mixed
         * When present sets the value against the $key
         * @return mixed
         * When value is null the current value stored against $key is returned.
         */
        public function request($key, $value = null){
            $request = $this->store('adapt.request');
            if (is_null($request)){
                $request = &$_REQUEST;
                $this->store('adapt.request', $request);
            }
            
            if (is_null($value)){
                return $request[$key];
            }else{
                $request[$key] = $value;
                $this->store('adapt.request', $request);
            }
        }
        
        /**
         * Returns the current response object.  Think of this as the opposite
         * to $_REQUEST.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @return array
         * An array of all responses currently waiting to send.
         */
        public function pget_response(){
            $responses = $this->store('adapt.response');
            
            if (is_array($responses)){
                return $responses;
            }elseif(isset($this->request['adapt_response'])){
                $responses = json_decode($this->request['adapt_response'], true);
                
                $this->store('adapt.response', $responses);
                return $responses;
            }
            
            return array();
        }
        
        /**
         * Allows actions to respond to requests.
         *
         * The data from this method is shared between all instances of
         * \adapt\base.
         *
         * @access public
         * @param string
         * The action (including the path) that you are responding too.
         * @param mixed
         * The data to send in the response. This is unstructured and can be
         * whatever you want it to be.
         */
        public function respond($action, $response){
            if ($action){
                $responses = $this->store('adapt.response');
                if (!is_array($responses)){
                    $responses = array();
                }
                
                $responses[$action] = $response;
                
                $this->store('adapt.response', $responses);
            }
        }
        
        /**
         * Files property.  This is the same as $_FILES.
         *
         * This property is shared between all instances of \adapt\base
         *
         * @access public
         * @return array
         * Returns an array equal to $_FILES.
         */
        public function pget_files(){
            $files = $this->store('adapt.files');
            if (is_null($files)){
                $files = &$_FILES;
                $this->store('adapt.files', $files);
            }
            
            return $files;
        }
        
        /**
         * Gets or sets a cookie.
         *
         * When only the first param is specified this method acts as a getter
         * and returns the cookie identified by $key.  When $value is set this
         * method acts as a getter.
         *
         * @access public
         * @param string
         * The name of the cookie to set or get.
         * @param string
         * The value that the cookie should be set too.
         * @param integer
         * The number of seconds this cookie is valid for. 0 denotes that this
         * is a session only cookie.
         * @param string
         * The path to restrict this cookie to.
         * @return string
         * The value of the cookie when $value is null.
         */
        public function cookie($key, $value = null, $expires = 0, $path = '/'){
            if (is_null($value)){
                if (isset($_COOKIE[$key])){
                    return $_COOKIE[$key];
                }
            }else{
                /* Set a cookie */
                setcookie($key, $value, $expires, $path);
            }
        }
        
        /**
         * Property for accessing the sanitizer.
         *
         * This property is shared between all instances of
         * \adapt\base.
         *
         * @access public
         * @return \adapt\sanitizer
         * Returns the global sanitizer object.
         */
        public function pget_sanitize(){
            $sanitizer = $this->store('adapt.sanitizer');
            if (is_null($sanitizer)){
                $sanitizer = new sanitizer();
                $this->store('adapt.sanitizer', $sanitizer);
            }
            
            return $sanitizer;
        }
        
        /**
         * Creates an instance of the class named $class
         *
         * @access public
         * @param string
         * The name of the class to be instanciated.
         * @return mixed
         * The created object.
         */
        public static function create_object($class){
            if (class_exists($class)){
                return new $class;
            }
        }
        
        /**
         * Extends a class at runtime to add new methods or properties.
         *
         * In this example we are going to add a new read-only property
         * to \adapt\base called new_prop.
         *
         * <code>
         * \adapt\base::extend(
         *      'pget_new_prop',
         *      function($_this){
         *          return "Value of property";
         *      }
         * );
         *
         * $adapt = new \adapt\base();
         * print $adapt->new_prop();
         * </code>
         *
         * @access public
         * @param string
         * The name of the property or method to add.
         * @param function
         * A function to handle the method or property.
         */
        public static function extend($name, $function){
            /*
             * We are going to store the
             * function in the adapt
             * store and then seek it
             * later
             */
            if (is_callable($function)){
                $class_name = get_called_class();
                $extension = $GLOBALS['adapt']->store('adapt.extensions');
                
                if (is_null($extension)) $extension = array();
                if (!isset($extension[$class_name])){
                    $extension[$class_name] = array();
                }
                //if (!isset($extension[$class_name][$name])){
                    $extension[$class_name][$name] = $function;
                //}
                
                $GLOBALS['adapt']->store("adapt.extensions", $extension);
            }
        }
        
        
        
    }
}


?>