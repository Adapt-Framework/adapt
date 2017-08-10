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
//global $time;
//$time = microtime();
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
//$adapt->dom = new \adapt\page();

/* Check if our inbound request is JSON and parse it */
$content_type = 'text/html';
if (isset($_SERVER['CONTENT_TYPE'])){
    list($content_type, $charset) = explode(";", $_SERVER['CONTENT_TYPE']);
}
if ($content_type == "application/json"){
    $json = @file_get_contents('php://input');

    if (is_json($json)){
        $request = $adapt->request;
        $json = json_decode($json, true);

        $request = array_merge($request, $json);
        $adapt->store('adapt.request', $request);
    }
}

/* Handle page caching and segmentation */
$cache_control = $adapt->cache->get("page_cache_control");
$cache_key = null;
$cache_time = $adapt->setting('adapt.page_cache_expires_after')?: 300;
if ($cache_control && is_array($cache_control) && 1==2){
    $found = false;
    $cache_key = "host/" . $_SERVER['HTTP_HOST'] .  "/pages/";// . sha1($adapt->request['url'] . "?");
    
    foreach($cache_control as $profile){
        if (preg_match("@{$profile['url_pattern']}@", "/" . $adapt->request['url'])){
            $found = true;
            
            if (!is_null($profile['cache_key'])){
                $cache_key .= $profile['cache_key'];
            }
            
            if ($profile['cache_time'] > 0){
                $cache_time = $profile['cache_time'];
                $segments = explode(";", $profile['segments']);
                if (count($segments)){
                    $segment_key = "";
                    foreach($segments as $segment){
                        if ($segment == "all"){
                            $segment_key = array_to_key($adapt->request);

                            foreach($_COOKIE as $key => $value){
                                $segment_key .= "{$key}={$value};";
                            }
                        }else{
                            $segment_key .= "{$segment}={$adapt->request[$segment]};";
                        }
                    }
                    $cache_key .= "/segments/" . sha1($segment_key);
                }
            }
            
            break;
        }
    }
    
    if (!$found){
        $cache_key .= sha1($adapt->request['url'] . "?");
        $segment_key = array_to_key($adapt->request);

        foreach($_COOKIE as $key => $value){
            $segment_key .= "{$key}={$value};";
        }
        
        $cache_key .= "/segments/" . sha1($segment_key);
    }
}
//print $cache_key;die();
/* Can we load from the cache? */
if (!is_null($cache_key)){
    $page = $adapt->cache->get($cache_key);
    
    if (!is_null($page)){
        $content_type = $adapt->cache->get_content_type($cache_key);
        if ($content_type){
            header("content-type: {$content_type}");
            print $page;
            exit(0);
        }
    }
}


/* Load the settings */
//$adapt->bundles->load_global_settings();
//$non_cachable_urls = $adapt->bundles->get_global_setting('adapt.non_cachable_urls');
//$url = "/" . $adapt->request['url'];
//$cachable = true;
//
//foreach($non_cachable_urls as $non_cachable_url){
//    if ($non_cachable_url == $url){
//        $cachable = false;
//        break;
//    }
//}

//print_r($adapt->request);die();
//print_r($non_cachable_urls);die();

/*
 * Is the current page cached?
 */
//$cachable = false;
//if ($cachable){
//    $request_key = array_to_key($adapt->request);
//
//    foreach($_COOKIE as $key => $value){
//        $request_key .= "{$key}={$value};";
//    }
//
//    $request_key = "pages/" . sha1($request_key);
//
//    $page = $adapt->cache->get($request_key);
//    if (!is_null($page)){
//        $content_type = $adapt->cache->get_content_type($request_key);
//        if ($content_type){
//            header("content-type: {$content_type}");
//            print $page;
//            exit(0);
//        }
//    }
//}

/*
 * Boot the system
 */
//$time_to_here = microtime() - $time;
//$time = microtime();
//print "Time to boot framework: {$time_to_here}\n";
if ($adapt->bundles->boot_application()){
    
    $adapt->bundles->save_bundle_cache();
    $adapt->bundles->save_global_settings();
    
    // Temp code
    $adapt->bundles->_cache_cache_control();
    
    //if (isset($_SERVER['SHELL'])){
    if (!isset($_SERVER['HTTP_HOST'])){
        /* Command Line Interface */
        print "Adapt Framework (" . ADAPT_VERSION . ") CLI\n";
        
        /* Fire the ready event */
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
                
                /* Remove the leading slash from nginx and route the URL */
                $output = $controller->route(rtrim($adapt->request['url'], '/'));
                $content_type = $controller->content_type;
                
                /* Set the content type */
                header("content-type: {$content_type}");
                
                if ($output){
                    
                    /* Cache the result */
                    if (strtolower($adapt->setting('adapt.disable_caching')) != "yes"){
                        $adapt->cache->page($cache_key, $output, $cache_time, $content_type);
                    }
                    
                    /* Send only the current output to the browser (Likely AJAX) */
                    print $output;
                }else{
                    /* Send out the whole page */
                    if ($adapt->dom){
                        $output = $adapt->dom;
                        if ($output instanceof \adapt\page){
                            $output = $output->render();
                        }
                        
                        /* Cache the result */
                        if (strtolower($adapt->setting('adapt.disable_caching')) != "yes"){
                            $adapt->cache->page($request_key, $output, $adapt->setting('adapt.page_cache_expires_after')?: 300, $content_type);
                        }
                        
                        print $output;
                    }
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
