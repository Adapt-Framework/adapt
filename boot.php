<?php

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

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

/*
 * Create a gloabl adapt object
 * that can be accessed from
 * $GLOBALS['adapt']
 */
global $adapt;
$adapt = new \frameworks\adapt\base();


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

/*
 * Add handlers
 */
$adapt->add_handler("\\frameworks\\adapt\\xml");
$adapt->add_handler("\\frameworks\\adapt\\html");
$adapt->add_handler("\\frameworks\\adapt\\model");

/*
 * Load settings
 */
$bundles = new \frameworks\adapt\bundles();
$bundle_adapt = $bundles->get('adapt');

if ($bundle_adapt && $bundle_adapt instanceof \frameworks\adapt\bundle){
    $bundle_adapt->apply_settings();
}


/*
 * Create the root view
 */
$adapt->dom = new \frameworks\adapt\page();

/*
 * Boot the active application
 */
$bundles->boot();


/*
 * Are we working via the
 * web or by CLI?
 */
if (isset($_SERVER['SHELL'])){
    /* Command Line Interface */
    print "Adapt Framework CLI\n";
    
}else{
    /* Web Session */
    $controller = null;
    
    /* Does the application have a root controller? */
    if (class_exists("\\application\\controller_root")){
        $controller = new \application\controller_root();
        
        /* Add the controllers view to the dom */
        $adapt->dom->add($controller->view);
    }
    
    if (isset($controller) && $controller instanceof \frameworks\adapt\controller){
        if (isset($adapt->request['actions'])){
            $actions = explode(",", $adapt->request['actions']);
            foreach($actions as $action){
                $controller->route($action, true);
            }
        }
        
        $output = $controller->route($adapt->request['url']);
        header("content-type: {$controller->content_type}");
        
        if ($output){
            print $output;
        }else{
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