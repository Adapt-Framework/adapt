<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2015 Matt Bruton
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
 */

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

/*
 * Load libraries
 */
if (is_dir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries")){
    $files = scandir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries", SCANDIR_SORT_ASCENDING);
    
    foreach($files as $file){
        if (preg_match("/\.php$/", $file)){
            require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries/" . $file);
        }
    }
}

/*
 * Create a gloabl adapt object
 * that can be accessed from
 * $GLOBALS['adapt']
 */
global $adapt;
$adapt = new \adapt\base();


/*
 * Create a global bundles object
 */
$adapt->bundles = new \adapt\bundles();


/*
 * Register the adapt namespaces manually
 */
$adapt->bundles->register_namespace("\\adapt", 'adapt', ADAPT_VERSION);
$adapt->bundles->register_namespace("\\adapt\\interfaces", 'adapt', ADAPT_VERSION);

/*
 * Load configuration
 */
if (is_dir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config")){
    $files = scandir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config", SCANDIR_SORT_ASCENDING);
    
    foreach($files as $file){
        if (preg_match("/\.php$/", $file)){
            require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config/" . $file);
        }
    }
}

/*
 * Add handlers
 */
$adapt->add_handler("\\adapt\\xml");
$adapt->add_handler("\\adapt\\html");
$adapt->add_handler("\\adapt\\model");
$adapt->add_handler("\\adapt\\sql");
$adapt->add_handler("\\adapt\\bundle");


/*
 * Define the file storage path
 */

/* Set the file path if it's not set */
$path = $adapt->setting('adapt.file_store_path');

if (is_null($path)){
    $path = ADAPT_PATH . "store/";
    $adapt->setting('adapt.file_store_path', $path);
}

/* Set the file store */
$adapt->file_store = new \adapt\storage_file_system();

/* Set the cache */
$adapt->cache = new \adapt\cache();

/* Set the dom */
$adapt->dom = new \adapt\page();

/*
 * Is the current page cached?
 */
if (!isset($adapt->request['actions'])){
    if ($_SERVER && is_array($_SERVER) && isset($_SERVER['REQUEST_URI'])){
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
}


/*
 * Boot the system
 */

if ($adapt->bundles->boot_application()){
    
    $adapt->bundles->save_bundle_cache();
    $adapt->bundles->save_global_settings();
    
    if (isset($_SERVER['SHELL'])){
        /* Command Line Interface */
        print "Adapt Framework (" . ADAPT_VERSION . ") CLI\n";
        
        /* Fire the ready event */
        //$adapt->trigger(\frameworks\adapt\base::EVENT_READY);
        $adapt->trigger(\adapt\base::EVENT_READY);
        
    }else{
        
        /* Do we have a root view controller? */
        if (class_exists("\\application\\controller_root")){
            
            $controller = new \application\controller_root();
            
            if ($controller && $controller instanceof \adapt\controller){
                
                /* Add the controllers view to the dom */
                $adapt->dom->add($controller->view);
                
                /* Fire the system ready event */
                $adapt->trigger(\adapt\base::EVENT_READY);
                
                /* Process actions */
                if (isset($adapt->request['actions'])){
                    $actions = explode(",", $adapt->request['actions']);
                    
                    for($i = 0; $i < count($actions); $i++){
                        $adapt->store('adapt.current_action', $actions[$i]);
                        if ($i < count($actions)){
                            $adapt->store('adapt.next_action', $actions[$i + 1]);
                        }else{
                            $adapt->remove_store('adapt.next_action');
                        }
                        $controller->route($actions[$i], true);
                    }
                    $adapt->remove_store('adapt.current_action');
                    $adapt->remove_store('adapt.next_action');
                    
                    /* Are we redirecting? */
                    if (!is_null($adapt->store('adapt.redirect'))){
                        header('location:' . $adapt->store('adapt.redirect'));
                        exit(0);
                    }
                }
                
                /* Route the URL */
                $output = $controller->route($adapt->request['url']);
                $content_type = $controller->content_type;
                
                /* Set the content type */
                header("content-type: {$content_type}");
                
                if ($output){
                    /* Send only the current output to the browser (Likely AJAX) */
                    print $output;
                }else{
                    /* Send out the whole page */
                    if ($adapt->dom instanceof \adapt\page && $adapt->dom->cache_time > 0){
                        if (!isset($this->request['actions'])){
                            /* We can cache the page */
                            //$key = $_SERVER['REQUEST_URI'];
                            //$adapt->cache->page($key, $output, $adapt->dom->cache_time, $content_type);
                        }
                    }
                    print $adapt->dom;
                }
                
            }else{
                $adapt->dom->add(new html_h1("Something isn't right here..."));
                $adapt->dom->add(new html_p("The root controller failed to load"));
                print $adapt->dom;
            }
            
        }else{
            $adapt->dom->add(new html_h1("Something isn't right here..."));
            $adapt->dom->add(new html_p("The root control could not be found."));
            print $adapt->dom;
        }
    }
    
}else{
    print "<pre>" . print_r($adapt->bundles->errors(), true) . "</pre>";
}

//exit(1); 
//
///*
// * Prevent direct access
// */
//defined('ADAPT_STARTED') or die;
////$time_offset = microtime(true);
//
//
///*
// * Load libraries
// */
//if (is_dir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries")){
////if (is_dir(FRAMEWORK_PATH . "adapt/libraries")){
//    $files = scandir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries", SCANDIR_SORT_ASCENDING);
//    //$files = scandir(FRAMEWORK_PATH . "adapt/libraries", SCANDIR_SORT_ASCENDING);
//    
//    foreach($files as $file){
//        if (preg_match("/\.php$/", $file)){
//            require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/libraries/" . $file);
//            //require_once(FRAMEWORK_PATH . "adapt/libraries/" . $file);
//        }
//    }
//}
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to load libraries: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Create a gloabl adapt object
// * that can be accessed from
// * $GLOBALS['adapt']
// */
//global $adapt;
////$adapt = new \frameworks\adapt\base();
//$adapt = new \adapt\base();
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to initial base: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Load configuration
// */
////if (is_dir(FRAMEWORK_PATH . "adapt/config")){
//if (is_dir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config")){
//    //$files = scandir(FRAMEWORK_PATH . "adapt/config", SCANDIR_SORT_ASCENDING);
//    $files = scandir(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config", SCANDIR_SORT_ASCENDING);
//    
//    foreach($files as $file){
//        if (preg_match("/\.php$/", $file)){
//            //require_once(FRAMEWORK_PATH . "adapt/config/" . $file);
//            require_once(ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/config/" . $file);
//        }
//    }
//}
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to load config: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Add handlers
// */
////$adapt->add_handler("\\frameworks\\adapt\\xml");
////$adapt->add_handler("\\frameworks\\adapt\\html");
////$adapt->add_handler("\\frameworks\\adapt\\model");
//$adapt->add_handler("\\adapt\\xml");
//$adapt->add_handler("\\adapt\\html");
//$adapt->add_handler("\\adapt\\model");
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to add handlers: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///* Set the file path if it's not set */
//$path = $adapt->setting('adapt.file_store_path');
//
//if (is_null($path)){
//    //$path = FRAMEWORK_PATH . 'adapt/store/';
//    $path = ADAPT_PATH . "adapt/adapt-" . ADAPT_VERSION . "/store/";
//    $adapt->setting('adapt.file_store_path', $path);
//}
//
///* Set the file store */
////$adapt->file_store = new \frameworks\adapt\storage_file_system();
//$adapt->file_store = new \adapt\storage_file_system();
//
///* Set the cache */
////$adapt->cache = new \frameworks\adapt\cache();
//$adapt->cache = new \adapt\cache();
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to initialise file_system &amp; cache: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Can we find a pre-booted system in the cache?
// */
////$_cache_global_adapt = $adapt->cache->get('adapt.global_adapt');
////$_cache_page = $adapt->cache->get('adapt.page');
//
////if (is_array($_cache_global_adapt) && !is_null($_cache_page)){
////    print "<pre>Found pre-booted system in cache</pre>";
////}
//
//
///*
// * Is the current page cached?
// */
//if (!isset($adapt->request['actions'])){
//    if ($_SERVER && is_array($_SERVER) && isset($_SERVER['REQUEST_URI'])){
//        $key = $_SERVER['REQUEST_URI'];
//        $page = $adapt->cache->get($key);
//        if ($page){
//            $content_type = $adapt->cache->get_content_type($key);
//            
//            if ($content_type){
//                header("content-type: {$content_type}");
//                print $page;
//                exit(0);
//            }
//        }
//    }
//}
//
///*
// * Load settings
// */
////$bundles = new \frameworks\adapt\bundles();
//$bundles = new \adapt\bundles();
////$bundle_adapt = $bundles->get('adapt');
//$bundles->register_namespace("adapt", 'adapt', ADAPT_VERSION);
//$bundle_adapt = $bundles->get_bundle('adapt', array(ADAPT_VERSION));
//
//if ($bundle_adapt && $bundle_adapt instanceof \adapt\bundle){
//    $bundle_adapt->apply_settings(); //TODO: <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< TODO <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//}
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to load settings: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Create the root view
// */
////if ($_SERVER && is_array($_SERVER)){
//    //$adapt->dom = new \frameworks\adapt\page();
//    $adapt->dom = new \adapt\page();
////}
//
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to load the dom: " . $time . "</pre>";
////$time_offset = microtime(true);
//
///*
// * Boot the active application
// */
////$bundles->boot();
//$bundles->boot_system();
//
///*
// * Cache the system
// */
////$adapt->cache->serialize('adapt.global_adapt', $GLOBALS['__adapt']);
////$adapt->cache->serialize('adapt.dom', $adapt->dom);
//
//
////$time = microtime(true) - $time_offset;
////print "<pre>Time to boot: " . $time . "</pre>";
////$time_offset = microtime(true);
///*
// * Are we working via the
// * web or by CLI?
// */
//if (isset($_SERVER['SHELL'])){
//    /* Command Line Interface */
//    print "Adapt Framework (" . ADAPT_VERSION . ") CLI\n";
//    
//    /* Fire the ready event */
//    //$adapt->trigger(\frameworks\adapt\base::EVENT_READY);
//    $adapt->trigger(\adapt\base::EVENT_READY);
//    
//}else{
//    /* Web Session */
//    $controller = null;
//    
//    /* Does the application have a root controller? */
//    if (class_exists("\\application\\controller_root")){
//        
//         /* Add the adapt controller to the root */
//        \application\controller_root::extend('view__adapt', function($_this){
//            //return $_this->load_controller("\\frameworks\\adapt\\controller_adapt");
//            return $_this->load_controller("\\adapt\\controller_adapt");
//        });
//        
//        $controller = new \application\controller_root();
//        
//        /* Add the controllers view to the dom */
//        $adapt->dom->add($controller->view);
//    }
//    
//    //if (isset($controller) && $controller instanceof \frameworks\adapt\controller){
//    if (isset($controller) && $controller instanceof \adapt\controller){
//        
//        //$adapt->trigger(\frameworks\adapt\base::EVENT_READY);
//        $adapt->trigger(\adapt\base::EVENT_READY);
//        
//        /* Process actions */
//        if (isset($adapt->request['actions'])){
//            $actions = explode(",", $adapt->request['actions']);
//            
//            for($i = 0; $i < count($actions); $i++){
//                $adapt->store('adapt.current_action', $actions[$i]);
//                if ($i < count($actions)){
//                    $adapt->store('adapt.next_action', $actions[$i + 1]);
//                }else{
//                    $adapt->remove_store('adapt.next_action');
//                }
//                $controller->route($actions[$i], true);
//            }
//            $adapt->remove_store('adapt.current_action');
//            $adapt->remove_store('adapt.next_action');
//            
//            /* Are we redirecting? */
//            if (!is_null($adapt->store('adapt.redirect'))){
//                header('location:' . $adapt->store('adapt.redirect'));
//                exit(0);
//            }
//            
//            //foreach($actions as $action){
//            //    $controller->route($action, true);
//            //}
//        }
//        
//        $output = $controller->route($adapt->request['url']);
//        $content_type = $controller->content_type;
//        /*if ($content_type == "text/html" && $adapt->dom instanceof \frameworks\adapt\html && $adapt->dom->tag == 'html'){
//            $adapt->dom->body->add(new html_pre("Sana me"));
//        }*/
//        header("content-type: {$content_type}");
//        
//        if ($output){
//            print $output;
//        }else{
//            //if ($adapt->dom instanceof \frameworks\adapt\page && $adapt->dom->cache_time > 0){
//            if ($adapt->dom instanceof \adapt\page && $adapt->dom->cache_time > 0){
//                if (!isset($this->request['actions'])){
//                    /* We can cache the page */
//                    $key = $_SERVER['REQUEST_URI'];
//                    $adapt->cache->page($key, $output, $adapt->dom->cache_time, $content_type);
//                }
//            }
//            print $adapt->dom;
//        }
//    }else{
//        //print "Unable to find root controller\n";
//        $adapt->dom->add(new html_h1('Unable to locate the root controller'));
//        
//        $errors = $bundles->errors(true);
//        if (is_array($errors) && count($errors)){
//            foreach($errors as $error){
//                $adapt->dom->add(new html_p($error));
//            }
//        }
//        print $adapt->dom;
//    }
//}