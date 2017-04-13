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
                    /* Send only the current output to the browser (Likely AJAX) */
                    print $output;
                }else{
                    /* Send out the whole page */
                    if ($adapt->dom instanceof \adapt\page && $adapt->dom->cache_time > 0){
                        if (!isset($this->request['actions'])){
                            /* We can cache the page */
                            
                            /*
                             * @todo: Review how this should work.
                             * In testing we get weird cache paths
                             * because we are using REQUEST_URI as
                             * the key but this isn't a valid cache
                             * path.
                             */
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
