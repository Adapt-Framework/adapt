<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class base{
        
        /* Events */
        const EVENT_ERROR = 'adapt.error';
        const EVENT_READY = 'adapt.ready';
        
        /* Private properties */
        protected $_instance_id;
        protected $_errors;
        protected $_event_handlers;
        
        /*
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
        
        /*
         * Get the instance ID
         */
        public function aget_instance_id(){
            return $this->_instance_id;
        }
        
        /*
         * Enable dynamic getting / setting
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
        
        /*
         * Class handlers
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
        
        /*
         * Error handling
         */
        public function error($error){
            if (isset($error)){
                $this->_errors[] = $error;
                $this->trigger(self::EVENT_ERROR, array('error' => $error));
            }
        }
        
        public function errors($clear = false){
            $errors = $this->_errors;
            if ($clear) $this->_errors = array();
            return $errors;
        }
        
        
        /*
         * Event handeling
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
        
        public function trigger($event_type, $event_data = array()){
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
        
        
        /*
         * Temporary storage
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
        
        public function remove_store($key){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['storage'])) $GLOBALS['__adapt']['storage'] = array();
            
            if (isset($GLOBALS['__adapt']['storage'][$key])){
                unset($GLOBALS['__adapt']['storage'][$key]);
            }
        }
        
        /*
         * Default settings
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
        
        public function get_settings(){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['settings'])) $GLOBALS['__adapt']['settings'] = array();
            
            return $GLOBALS['__adapt']['settings'];
        }
        
        public function remove_setting($key){
            if (!isset($GLOBALS['__adapt'])) $GLOBALS['__adapt'] = array();
            if (!isset($GLOBALS['__adapt']['settings'])) $GLOBALS['__adapt']['settings'] = array();
            
            if (isset($GLOBALS['__adapt']['settings'][$key])){
                unset($GLOBALS['__adapt']['settings'][$key]);
            }
        }
        
        /*
         * Return the DOM
         * (Document Object Model)
         */
        public function pget_dom(){
            return $this->store('adapt.root_view');
        }
        
        public function pset_dom($view){
            $this->store('adapt.root_view', $view);
        }
        
        /*
         * Session property
         */
        //public function aget_session(){
        //    return $this->store('adapt.session');
        //}
        
        /*
         * Notifcations
         * - Form errors
         * - Messages
         * - Etc...
         */
        //public function aget_notifications(){
        //    $notifications = $this->store('adapt.notifications');
        //    if (is_null($notifications)){
        //        $notifications = new \application\notifications();
        //        $this->store('adapt.notifications', $notifications);
        //    }
        //    return $notifications;
        //}
        
        /*
         * Platform datasource property
         */
        public function aget_data_source(){
            return $this->store('adapt.data_source');
        }
        
        public function aset_data_source($data_source){
            $this->store('adapt.data_source', $data_source);
        }
        
        /*
         * Request property
         */
        public function aget_request(){
            $request = $this->store('adapt.request');
            if (is_null($request)){
                $request = &$_REQUEST;
                $this->store('adapt.request', $request);
            }
            
            return $request;
        }
        
        /*
         * Files property
         */
        public function aget_files(){
            $files = $this->store('adapt.files');
            if (is_null($files)){
                $files = &$_FILES;
                $this->store('adapt.files', $files);
            }
            
            return $files;
        }
        
        /*
         * Cookie functions
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
        
        /*
         * Global sanitizer property
         */
        public function aget_sanitize(){
            $sanitizer = $this->store('adapt.sanitizer');
            if (is_null($sanitizer)){
                $sanitizer = new \frameworks\adapt\sanitizer();
                $this->store('adapt.sanitizer', $sanitizer);
            }
            
            return $sanitizer;
        }
        
        /*
         * Static functions
         */
        public static function create_object($class){
            if (class_exists($class)){
                return new $class;
            }
        }
        
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
                if (!isset($extension[$class_name][$name])){
                    $extension[$class_name][$name] = $function;
                }
                
                $GLOBALS['adapt']->store("adapt.extensions", $extension);
            }
        }
        
        
        
    }
}


?>