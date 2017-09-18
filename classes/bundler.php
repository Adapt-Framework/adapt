<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2017 Matt Bruton
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
 * @copyright   2017 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 *
 */

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Changes a bundle into a bundle file to be transported to
     * the Adapt repository.
     */
    class bundler extends base{
        
        /**
         * Converts a bundle into a bundle file.
         * 
         * @param type $bundle_path
         * The path of the bundle to be bundled.
         * @param type $output_path
         * The path to where the output bundle file should be written.
         * @return boolean
         * Returns true if the bundler succeeded.
         */
        public function bundle($bundle_path, $output_path){
            if (!$bundle_path){
                $this->error("Bundle path is required");
                return false;
            }
            
            if (!$output_path){
                $this->error("Output path is required");
                return false;
            }
            
            $manifest = array();
            $this->process_directory($bundle_path, "/", $manifest);
            
            $encoded = json_encode($manifest);

            $ofp = fopen($output_path, "w");
            if ($ofp){
                fputs($ofp, $encoded . "\n");
                $base = trim($bundle_path, "/");
                foreach($manifest as $file){
                    $ifp = fopen($base . "/" . $file['name'], "r");
                    if ($ifp){
                        fwrite($ofp, fread($ifp, $file['length']));
                    }
                    fclose($ifp);
                }
            }

            fclose($ofp);
            
            return true;
        }
        
        /** @ignore */
        protected function process_directory($dir, $path, &$file_list){
            $dir = trim($dir, "/");
            $files = scandir($dir);

            foreach($files as $file){
                if (!preg_match("/^\./", $file)){

                    if (is_dir($dir . "/" . $file)){
                        $this->process_directory($dir . '/' . $file, $path . $file . '/', $file_list);
                    }else{
                        $file_list[] = array(
                            'name' => trim($path . $file, "/"),
                            'length' => filesize($dir . "/" . $file)
                        );
                    }
                }

            }
        }
    }
    
}
