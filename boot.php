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
//$time_offset = microtime(true);


/*
 * Load libraries
 */
if (is_dir(FRAMEWORK_PATH . "adapt/libraries")){
    $files = scandir(FRAMEWORK_PATH . "adapt/libraries", SCANDIR_SORT_ASCENDING);
    
    foreach($files as $file){
        if (preg_match("/\.php$/", $file)){
            require_once(FRAMEWORK_PATH . "adapt/libraries/" . $file);
        }
    }
}

//$time = microtime(true) - $time_offset;
//print "<pre>Time to load libraries: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Create a gloabl adapt object
 * that can be accessed from
 * $GLOBALS['adapt']
 */
global $adapt;
$adapt = new \frameworks\adapt\base();

//$time = microtime(true) - $time_offset;
//print "<pre>Time to initial base: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Load configuration
 */
if (is_dir(FRAMEWORK_PATH . "adapt/config")){
    $files = scandir(FRAMEWORK_PATH . "adapt/config", SCANDIR_SORT_ASCENDING);
    
    foreach($files as $file){
        if (preg_match("/\.php$/", $file)){
            require_once(FRAMEWORK_PATH . "adapt/config/" . $file);
        }
    }
}

//$time = microtime(true) - $time_offset;
//print "<pre>Time to load config: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Add handlers
 */
$adapt->add_handler("\\frameworks\\adapt\\xml");
$adapt->add_handler("\\frameworks\\adapt\\html");
$adapt->add_handler("\\frameworks\\adapt\\model");

//$time = microtime(true) - $time_offset;
//print "<pre>Time to add handlers: " . $time . "</pre>";
//$time_offset = microtime(true);

/* Set the file path if it's not set */
$path = $adapt->setting('adapt.file_store_path');

if (is_null($path)){
    $path = FRAMEWORK_PATH . 'adapt/store/';
    $adapt->setting('adapt.file_store_path', $path);
}

/* Set the file store */
$adapt->file_store = new \frameworks\adapt\storage_file_system();

/* Set the cache */
$adapt->cache = new \frameworks\adapt\cache();

//$time = microtime(true) - $time_offset;
//print "<pre>Time to initialise file_system &amp; cache: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Can we find a pre-booted system in the cache?
 */
//$_cache_global_adapt = $adapt->cache->get('adapt.global_adapt');
//$_cache_page = $adapt->cache->get('adapt.page');

//if (is_array($_cache_global_adapt) && !is_null($_cache_page)){
//    print "<pre>Found pre-booted system in cache</pre>";
//}


/*
 * Is the current page cached?
 */
if (!isset($adapt->request['actions'])){
    $key = $_SERVER['REQUEST_URI'];
    $page = $adapt->cache->get($key);
    if ($page){
        $content_type = $adapt->cache->get_content_type($key);
        
        if ($content_type){
            header("content-type: {$content_type}");
            print $page;
            exit(0);
        }
    }
}

/*
 * Load settings
 */
$bundles = new \frameworks\adapt\bundles();
$bundle_adapt = $bundles->get('adapt');

if ($bundle_adapt && $bundle_adapt instanceof \frameworks\adapt\bundle){
    $bundle_adapt->apply_settings();
}

//$time = microtime(true) - $time_offset;
//print "<pre>Time to load settings: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Create the root view
 */
$adapt->dom = new \frameworks\adapt\page();

//$time = microtime(true) - $time_offset;
//print "<pre>Time to load the dom: " . $time . "</pre>";
//$time_offset = microtime(true);

/*
 * Boot the active application
 */
$bundles->boot();

/*
 * Cache the system
 */
//$adapt->cache->serialize('adapt.global_adapt', $GLOBALS['__adapt']);
//$adapt->cache->serialize('adapt.dom', $adapt->dom);


//$time = microtime(true) - $time_offset;
//print "<pre>Time to boot: " . $time . "</pre>";
//$time_offset = microtime(true);
/*
 * Are we working via the
 * web or by CLI?
 */
if (isset($_SERVER['SHELL'])){
    /* Command Line Interface */
    print "Adapt Framework CLI\n";
    
    /* Fire the ready event */
    $adapt->trigger(\frameworks\adapt\base::EVENT_READY);
    
}else{
    /* Web Session */
    $controller = null;
    
    /* Does the application have a root controller? */
    if (class_exists("\\application\\controller_root")){
        
         /* Add the adapt controller to the root */
        \application\controller_root::extend('view__adapt', function($_this){
            return $_this->load_controller("\\frameworks\\adapt\\controller_adapt");
        });
        
        $controller = new \application\controller_root();
        
        /* Add the controllers view to the dom */
        $adapt->dom->add($controller->view);
    }
    
    if (isset($controller) && $controller instanceof \frameworks\adapt\controller){
        
        $adapt->trigger(\frameworks\adapt\base::EVENT_READY);
        
        /* Process actions */
        if (isset($adapt->request['actions'])){
            $actions = explode(",", $adapt->request['actions']);
            
            foreach($actions as $action){
                $controller->route($action, true);
            }
        }
        
        $output = $controller->route($adapt->request['url']);
        $content_type = $controller->content_type;
        /*if ($content_type == "text/html" && $adapt->dom instanceof \frameworks\adapt\html && $adapt->dom->tag == 'html'){
            $adapt->dom->body->add(new html_pre("Sana me"));
        }*/
        header("content-type: {$content_type}");
        
        if ($output){
            print $output;
        }else{
            if ($adapt->dom instanceof \frameworks\adapt\page && $adapt->dom->cache_time > 0){
                if (!isset($this->request['actions'])){
                    /* We can cache the page */
                    $key = $_SERVER['REQUEST_URI'];
                    $adapt->cache->page($key, $output, $adapt->dom->cache_time, $content_type);
                }
            }
            print $adapt->dom;
        }
    }else{
        //print "Unable to find root controller\n";
        $adapt->dom->add(new html_h1('Unable to locate the root controller'));
        
        $errors = $bundles->errors(true);
        if (is_array($errors) && count($errors)){
            foreach($errors as $error){
                $adapt->dom->add(new html_p($error));
            }
        }
        print $adapt->dom;
    }
}


?>