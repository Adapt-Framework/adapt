<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2015 Adapt Framework (www.adaptframework.com)
 * Authored by Matt Bruton (matt@adaptframework.com)
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
 */

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
    $registered_namespaces = $adapt->store('adapt.namespaces');
    
    if (count($namespaces) && count($registered_namespaces)){
        if ($namespaces[0] == "application"){
            /* Alias the application bundle */
            $bundle_name = strtolower($namespaces[0]);
            $application = $adapt->setting('adapt.running_application');
            if (isset($application)){
                $class_def = "namespace application{ class {$class_name} extends {$application}\\{$class_name}{} }";
                eval($class_def);
                $class_loaded = true;
            }
        }else{
            /* Check against registered namespaces */
            $requested_namespace = "\\" . implode("\\", $namespaces);
            
            $path = ADAPT_PATH . "{$registered_namespaces[$requested_namespace]['bundle_name']}/{$registered_namespaces[$requested_namespace]['bundle_name']}-{$registered_namespaces[$requested_namespace]['bundle_version']}/";
            
            $locations = array('classes/', 'views/', 'controllers/', 'models/', 'interfaces/');
            foreach($locations as $location){
                if (file_exists($path . $location . $class_name . ".php")){
                    require_once($path . $location . $class_name . ".php");
                    $class_loaded = true;
                }
            }
        }
    }
    
    /*
     * We haven't been able to find the class,
     * if the class is a model it may have been
     * declared in another bundle, so lets check.
     */
    if ($class_loaded == false && substr($class_name, 0, 5) == 'model'){
        if (is_array($registered_namespaces) && count($registered_namespaces)/* && count($namespaces)*/){
            $requested_namespace = implode("\\", $namespaces);
            
            foreach($registered_namespaces as $nskey => $registered_namespace){
                $path = ADAPT_PATH . "{$registered_namespace['bundle_name']}/{$registered_namespace['bundle_name']}-{$registered_namespace['bundle_version']}/models/{$class_name}.php";
                //print "<pre>{$path}</pre>";
                if (file_exists($path)){
                    $class_def = "class {$class_name} extends {$nskey}\\{$class_name}{}";
                    if ($requested_namespace != ""){
                        $class_def = "namespace {$requested_namespace}{{$class_def}}";
                    }
                    
                    eval($class_def);
                    $class_loaded = true;
                }
            }
        }
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
                    $class_def .= "call_user_func_array(array('parent', __FUNCTION__), \$params);";
                    
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
