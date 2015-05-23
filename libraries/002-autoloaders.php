<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

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
        
        if ($bundle instanceof \frameworks\adapt\bundle && (($bundle->installed == 'Yes' && ($bundle->booted == 'Yes' || $namespaces[0] == 'applications')) || $bundle->bundle_name == 'adapt')){
            if (isset($namespaces[2]) && $namespaces[2] == 'interfaces'){
                if (file_exists($bundle->path . 'interfaces/' . $class_name . '.php')){
                    require_once($bundle->path . 'interfaces/' . $class_name . '.php');
                    return null;
                }
            }else{
                foreach($paths as $path){
                    
                    if (file_exists($bundle->path . $path . $class_name . '.php')){
                        /* Do we have a template for this class? */
                        $templates = $bundles->list_templates();
                        foreach($templates as $template){
                            if (file_exists(TEMPLATE_PATH . $template . $bundle->name . '/' . $path . $class_name . '.php')){
                                require_once(TEMPLATE_PATH . $template . $bundle->name . '/' . $path . $class_name . '.php');
                                return null;
                            }
                        }
                        
                        require_once($bundle->path . $path . $class_name . '.php');
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