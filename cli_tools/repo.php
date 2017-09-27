#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_PARSE);

define('TEMP_PATH', sys_get_temp_dir() . '/');
define('ADAPT_PATH', "../../../");
define('ADAPT_VERSION', "2.1.0");
define('ADAPT_STARTED', true);
require(ADAPT_PATH . 'adapt/adapt-' . ADAPT_VERSION . '/boot.php');

$adapt = $GLOBALS['adapt'];
$application = $adapt->bundles->load_bundle($adapt->setting('adapt.default_application_name'));

if (count($argv) == 1) {
    print "\033[0;37mCurrent application:\n";
    print "\033[1;37m\t{$application->name} v{$application->version}\n";
    print "\033[0;37m\nUsage:\n";
    print "\033[1;32m\t{$argv[0]} [options] [bundle name]\n";
    print "\n";
    print "\033[0;37mOptions:\n";
    print "\033[1;37m\t-v\t\t\t\033[0;37mVerbose\n";
    print "\033[1;37m\t-r\t\t\t\033[0;37mUpdate system to latest revision\n";
    print "\033[1;37m\t-u\t\t\t\033[0;37mUpgrade application to latest version\n";
    print "\033[1;37m\t-p\t\t\t\033[0;37mPublish bundle\n";
    print "\n";
    exit(1);
}

$options = $argv[1];
$bundle_name = $argv[2];

$verbose = false;
if (strpos($options, "v")){
    $verbose = true;
}

if (strpos($options, "r")){
    if ($verbose) print "Preparing to update the system\n";
    
    $sql = $adapt->data_source->sql;
    $sql->select('name')
        ->from('bundle')
        ->where(
            new sql_and(
                new sql_cond('date_deleted', sql::IS, sql::NULL)
            )
        );
    $results = $sql->execute()->results();
    
    foreach($results as $result){
        $bundle = $adapt->bundles->load_bundle($result['name']);
        if ($verbose) print "\033[0;37mUpdating \033[1;37m{$result['name']} \033[0;37mfrom \033[1;37mv{$bundle->version}\033[0;37m";
        $version = $bundle->update($bundle->version);
        if ($version === false){
            if ($verbose) print " \033[1;31mNo updates\033[0;37m\n";
        }else{
            if ($verbose) print " to \033[1;32m{$version}\033[0;37m\n";
        }
    }
}

if (strpos($options, "u")){
    if ($verbose) print "Upgrading \033[1;37m\t{$application->name}\033[0;37m\n";
    
    $latest_version = $application->upgrade();
    if ($latest_version === false){
        if ($verbose) print "\033[1;31mNo upgrade available for this application\n\033[0;37m";
    }else{
        if ($verbose) print "\033[0;37mApplication was upgraded to \033[1;37mv{$latest_version}\033[0;37m\n";
    }
}

if (strpos($options, "p")){
    if (!$bundle_name){
        print "\033[1;31mBundle name required\033[0;37m\n";
        exit(1);
    }
    
    if (!is_dir(ADAPT_PATH . $bundle_name)){
        print "\033[1;31mUnknown bundle '{$bundle_name}'\033[0;37m\n";
        exit(1);
    }
    
    $dirs = scandir(ADAPT_PATH . $bundle_name);
    $versions = [];
    foreach($dirs as $dir){
        if (substr($dir, 0, strlen($bundle_name)) == $bundle_name){
            list($name, $version) = explode("-", $dir);
            if (preg_match("/^[0-9]+\.[0-9]+\.[0-9]+$/", $version)){
                $versions[] = $version;
            }
        }
    }
    
    $latest_version = \adapt\bundles::get_newest_version($versions);
    
    /* Get the bundle.xml */
    $xml = file_get_contents(ADAPT_PATH . "/{$bundle_name}/{$bundle_name}-{$latest_version}/bundle.xml");
    $xml = \adapt\xml::parse($xml);
    
    if (!$xml instanceof \adapt\xml){
        print "\033[1;31mUnable to read the bundle.xml for {$bundle_name}-{$latest_version}\033[0;37m\n";
        exit(1);
    }
    
    /* Bundle the bundle */
    $bundler = new \adapt\bundler();
    
    if (!$bundler->bundle(ADAPT_PATH . "/{$bundle_name}/{$bundle_name}-{$latest_version}", TEMP_PATH . "{$bundle_name}-{$latest_version}.bundle")){
        print "\033[1;31mUnable to create the bundle file for {$bundle_name}-{$latest_version}\033[0;37m\n";
        exit(1);
    }
    
    /* Publish the bundle */
    $response = $adapt->bundles->repository->create_bundle(TEMP_PATH . "{$bundle_name}-{$latest_version}.bundle");
    
    if (!$response){
        $errors = $adapt->bundles->repository->errors(true);
        foreach($errors as $error){
            print "\033[1;31m{$error}\033[0;37m\n";
        }
        exit(1);
    }
}
