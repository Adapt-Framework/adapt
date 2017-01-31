<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
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
 * @package     adapt
 * @author      Matt Bruton <matt.bruton@gmail.com>
 * @copyright   2016 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 *
 */

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Bundle class for Adapt framework
     */
    class bundle_adapt extends bundle {
        
        /**
         * Constructor
         *
         * @access public
         * @param xml $bundle_data
         * The contents of the bundles bundle.xml file.
         */
        public function __construct($bundle_data){
            parent::__construct('adapt', $bundle_data);
        }
        
        /**
         * Boots the adapt bundle.
         *
         * @access public
         * @return boolean
         * Returns true if the bundle booted successfully.
         */
        public function boot(){
            if (parent::boot()){
                
                /* Extend the root controller and add adapts controller  */
                \application\controller_root::extend('view__adapt', function($_this){
                    return $_this->load_controller("\\adapt\\controller_adapt");
                });
                
                /* Define the DOM */
                $this->dom = new page();
                
                /* Yay! */
                return true;
            }
            
            return false;
        }
        
        /**
         * Updates Adapt to the latest revision
         */
        public function update() {
            $latest_version = parent::update();
            
            if ($latest_version === false){
                return false;
            }
            
            // We need to change the index.php file to boot
            // the latest version
            $index_path = $_SERVER['DOCUMENT_ROOT'] . "/index.php";
            print $index_path . "\n";die();
            $fp = fopen($index_path, "w+b");
            if (!$fp){
                $this->error("Unable to write to index.php");
                return false;
            }
            
            fwrite($fp, "<?php\n");
            fwrite($fp, "define('TEMP_PATH', sys_get_temp_dir() . '/');\n");
            fwrite($fp, "define('ADAPT_PATH', \$_SERVER['DOCUMENT_ROOT'] . '/adapt/');\n");
            fwrite($fp, "define('ADAPT_VERSION', '{$latest_version}');\n");
            fwrite($fp, "define('ADAPT_STARTED', true);\n");
            fwrite($fp, "require(ADAPT_PATH . 'adapt/adapt-' . ADAPT_VERSION . '/boot.php');\n");
            
            fclose($fp);
            
            return $latest_version;
        }
    }
}
