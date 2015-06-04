<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

function voodoo($class){
    $class_loaded = false;
    
    /* Get a reference to adapt */
    $adapt = $GLOBALS['adapt'];
    
    /* Get the namespace and class name */
    $namespaces = explode("\\", $class);
    $class_name = array_pop($namespaces);
    
    //print_r($namespaces);
    //print "Class: {$class_name}\n";
    
    if (count($namespaces) > 0){
        $bundle_type = strtolower($namespaces[0]);
        
        $simple_types = array('applications', 'extensions', 'frameworks');
        //$complex_types = array('templates'); This namespace will never be used because templates use the origins namespace!
        $alias_types = array('application');
        
        if (in_array($bundle_type, $simple_types) && count($namespaces) >= 2){
            $bundle_name = strtolower($namespaces[1]);
            
            $bundle_name = strtolower($namespaces[1]);
            
            /*
             * Get a list of templates
             */
            $templates = scandir(TEMPLATE_PATH);
            
            if (is_array($templates) && (count($templates))){
                foreach($templates as $template){
                    if (substr($template, 0, 1) != "."){
                        
                        $locations = array('views', 'controllers', 'models');
                        
                        foreach($locations as $location){
                            if (file_exists(TEMPLATE_PATH . "{$template}/{$bundle_name}/{$location}/{$class_name}.php")){
                                require_once(TEMPLATE_PATH . "{$template}/{$bundle_name}/{$location}/{$class_name}.php");
                                $class_loaded = true;
                            }
                        }
                    }
                }
            }
            
            if ($class_loaded == false){
                $locations = array('classes', 'views', 'controllers', 'models');
                
                if (count($namespaces) >= 3 && strtolower($namespaces[2]) == 'interfaces'){
                    $locations = array('interfaces');
                }
                
                foreach($locations as $location){
                    if (file_exists(ADAPT_PATH . "{$bundle_type}/{$bundle_name}/{$location}/{$class_name}.php")){
                        require_once(ADAPT_PATH . "{$bundle_type}/{$bundle_name}/{$location}/{$class_name}.php");
                        $class_loaded = true;
                    }
                }
            }
            
            
        }elseif (in_array($bundle_type, $alias_types)){
            $bundle_name = strtolower($namespaces[1]);
            $application = $adapt->setting('adapt.running_application');
            
            if (isset($application)){
                $class_def = "namespace application{ class {$class_name} extends \\applications\\{$application}\\{$class_name}{} }";
                eval($class_def);
                $class_loaded = true;
            }
        }
    }else{
        //No Namespace
    }
    
    /*
     * If we still haven't loaded then
     * we may be able to use a handler to
     * do the job
     */
    if ($class_loaded == false){
        $handlers = $adapt->store('adapt.handlers');
        
        if (is_array($handlers) && count($handlers)){
            foreach($handlers as $handler){
                $handler = trim($handler, "\\");
                $handler_namespace = explode("\\", $handler);
                $handler_class_name = array_pop($handler_namespace);
                if (substr($class_name, 0, strlen($handler_class_name)) == $handler_class_name){
                    $name = trim(substr($class_name, strlen($handler_class_name) + 1), "_");

                    $class_def = "\$params = array_reverse(func_get_args());";
                    $class_def .= "\$params[] = \"{$name}\";";
                    $class_def .= "\$params = array_reverse(\$params);";
                    
                    //$class_def .= "\$reflector = new ReflectionClass(get_class(\$this));";
                    //$class_def .= "\$parent = \$reflector->getParentClass();";
                    //$class_def .= "\$method = \$parent->getMethod('__construct');";
                    //$class_def .= "\$method->invokeArgs(\$this, \$params);";
                    
                    $class_def .= "call_user_func_array(array('parent', __FUNCTION__), \$params);";
                    //$class_def .= "call_user_func_array(array(get_parent_class(\$this), \"__construct\"), \$params);";
                    
                    $class_def = "public function __construct(){{$class_def}}";
                    
                    
                    $class_def = "class {$class_name} extends \\{$handler}{ {$class_def}}";
                    
                    $namespace = implode("\\", $namespaces);
                    
                    if (strlen($namespace) > 0){
                        $class_def = "namespace {$namespace}{{$class_def}}";
                    }
                    
                    eval($class_def);
                    break;
                }
            }
        }
    }
}




function adapt_autoloader($class){
    //print "<pre>Auto loading: {$class}</pre>";
    $adapt = $GLOBALS['adapt'];
    
    $paths = array(
        'classes/',
        'models/',
        'views/',
        'controllers/',
        'interfaces/'
    );
    
    
    $namespaces = explode("\\", $class);
    $class_name = array_pop($namespaces);
    
    if (count($namespaces) && in_array($namespaces[0], array('frameworks', 'extensions', 'applications', 'application'))){
        if ($namespaces[0] == 'application'){
            /* We are going to alias the running application */
            $application = $adapt->store('adapt.running_application');
            
            if (isset($application)){
                $class = "namespace application{ class {$class_name} extends \\applications\\{$application}\\{$class_name}{} }";
                eval($class);
            }
            
            return null;
        }
        
        $bundles = new \frameworks\adapt\bundles();
        $bundle = $bundles->get_bundle($namespaces[1]);
        
        //if ($bundle instanceof \frameworks\adapt\bundle && (($bundle->installed == 'Yes' && ($bundle->booted == 'Yes' || $namespaces[0] == 'applications')) || $bundle->bundle_name == 'adapt')){
        if ($bundle instanceof \frameworks\adapt\bundle){    
            if (isset($namespaces[2]) && $namespaces[2] == 'interfaces'){
                if (file_exists($bundle->bundle_path . 'interfaces/' . $class_name . '.php')){
                    require_once($bundle->bundle_path . 'interfaces/' . $class_name . '.php');
                    return null;
                }
            }else{
                foreach($paths as $path){
                    
                    //print "<pre>{$bundle->path}{$path}{$class_name}.php</pre>";
                    
                    if (file_exists($bundle->bundle_path . $path . $class_name . '.php')){
                        /* Do we have a template for this class? */
                        $templates = $bundles->list_templates();
                        foreach($templates as $template){
                            if (file_exists(TEMPLATE_PATH . $template . $bundle->name . '/' . $path . $class_name . '.php')){
                                require_once(TEMPLATE_PATH . $template . $bundle->name . '/' . $path . $class_name . '.php');
                                return null;
                            }
                        }
                        
                        require_once($bundle->bundle_path . $path . $class_name . '.php');
                        return null;
                    }
                }
            }
                
            /* We haven't been able to find
             * the file for this class so
             * we need to check if we have
             * a handler for it instead.
             */
            $handlers = $adapt->store('adapt.handlers');
            if (is_array($handlers)){
                $handler_names = array_keys($handlers);
                $namespace = implode("\\", $namespaces);
                
                foreach($handler_names as $name){
                    //print "<pre>HANDELER: {$class_name}</pre>";
                    if (substr($class_name, 0, strlen($name)) == $name){
                        $handler = $handlers[$name];
                        $handler = str_replace("{{NAMESPACE}}", $namespace, $handler);
                        $handler = str_replace("{{CLASS}}", $class_name, $handler);
                        if (strlen($class_name) > strlen($name)){
                            $node_name = substr($class_name, strlen($name));
                            $handler = str_replace("{{NAME}}", $node_name, $handler);
                        }
                        eval($handler);
                        return null;
                    }
                }
            }
        }
        
    }elseif(count($namespaces) == 0){
        if (!find_class($class_name)){
            /* Ok, so we have been unable to load the class
             * but all is not lost, if this is a model it
             * may not have been defined
             */
            $handlers = $adapt->store('adapt.handlers_without_namespace');
            if (is_array($handlers)){
                $handler_names = array_keys($handlers);
                $namespace = implode("\\", $namespaces);
                
                foreach($handler_names as $name){
                    if (substr($class_name, 0, strlen($name)) == $name){
                        $handler = $handlers[$name];
                        $handler = str_replace("{{NAMESPACE}}", $namespace, $handler);
                        $handler = str_replace("{{CLASS}}", $class_name, $handler);
                        
                        if (strlen($class_name) > strlen($name)){
                            $node_name = substr($class_name, strlen($name));
                            $handler = str_replace("{{NAME}}", $node_name, $handler);
                        }
                        //print "<pre>{$handler}</pre>";
                        eval($handler);
                        return null;
                    }
                }
            }
        }
    }
    
    return null;
}

function find_class($class_name){
    $bundles = new \frameworks\adapt\bundles();
    
    $filename = "{$class_name}.php";
    $bundle_names = array(
        'applications' => $bundles->list_bundles(APPLICATION_PATH),
        'extensions' => $bundles->list_bundles(EXTENSION_PATH),
        'frameworks' => $bundles->list_bundles(FRAMEWORK_PATH)
    );
    $paths = array(
        'models',
        'views',
        'controllers',
        'classes',
        'interfaces'
    );
    
    foreach($bundle_names as $type => $names){
        $path = '';
        switch($type){
        case 'applications':
            $path = APPLICATION_PATH;
            break;
        case 'extensions':
            $path = EXTENSION_PATH;
            break;
        case 'frameworks':
            $path = FRAMEWORK_PATH;
            break;
        }
        
        foreach($names as $name){
            foreach($paths as $p){
                //print "<pre>{$path}{$name}/{$p}/{$filename}</pre>";
                if (file_exists("{$path}{$name}/{$p}/{$filename}")){
                    //print "<pre>Found!</pre>";
                    $alias = "class {$class_name} extends \\{$type}\\{$name}\\{$class_name}{ }";
                    eval($alias);
                    return true;
                }
            }
            
        }
    }
    
    return false;
}

?>