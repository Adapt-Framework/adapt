<?php

namespace adapt{

    /**
     * Description of unbundler
     *
     * @author Matt Bruton
     */
    class unbundler extends base{
        
        protected $_is_loaded;
        protected $_file_index;
        protected $_file_key;
        
        public function __construct($file_key = null){
            parent::__construct();
            $this->_is_loaded = false;
            $this->_file_index = [];
            if ($file_key) $this->load($file_key);
        }
        
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        public function pget_file_index(){
            return $this->_file_index;
        }
        
        public function load($file_key){
            // Load the file from the file store and into temp
            $file_path = $this->file_store->write_to_file($file_key);
            
            if (!file_exists($file_path) && filesize($file_path) > 0){
                $this->error("Unable to find bundle or bundle is empty");
                return false;
            }
            
            // Open the file
            $ftp = fopen($file_path, "r");
            
            // Check the file opened
            if (!$fp){
                $this->error("Unable to read the temp file {$file_path}");
                return false;
            }
            
            // Extract the bundles manifest
            $bundle_manifest = fgets($fp);
            
            // Check we have something
            if (strlen($bundle_manifest) == 0){
                $this->error("Unable to read the bundles manifest");
                return false;
            }
            
            // Check it's json
            if (!is_json($bundle_manifest)){
                $this->error("Bundle manifest is not valid JSON");
                return false;
            }
            
            // Parse the JSON
            $files_index = json_decode($bundle_manifest, true);
            
            // Check the index is an array
            if (!is_array($files_index)){
                $this->error("The file index is not valid");
                return false;
            }
            
            // Make the index publicly available
            $this->_file_index = $files_index;
            
            // Set to loaded
            $this->_is_loaded = true;
            
            // Close the file
            fclose($fp);
            
            // Unlink the temp file
            unlink($file_path);
            
            // Return success
            return true;
        }
        
        public function extract_all($path){
            
        }
        
        public function extract_file($file_name){
            if ($this->is_loaded){
                $file_path = $this->file_store->write_to_file($this->_file_key);
                
                if (!$file_path || file_size($file_path) == 0){
                    $this->error('Unable to open bundle');
                    return false;
                }
                
                // Open the file
                $ftp = fopen($file_path, "r");

                // Check the file opened
                if (!$fp){
                    $this->error("Unable to read the temp file {$file_path}");
                    return false;
                }

                // Extract the bundles manifest
                $bundle_manifest = fgets($fp);

                // Check we have something
                if (strlen($bundle_manifest) == 0){
                    $this->error("Unable to read the bundles manifest");
                    return false;
                }

                // Check it's json
                if (!is_json($bundle_manifest)){
                    $this->error("Bundle manifest is not valid JSON");
                    return false;
                }

                // Parse the JSON
                $files_index = json_decode($bundle_manifest, true);

                // Check the index is an array
                if (!is_array($files_index)){
                    $this->error("The file index is not valid");
                    return false;
                }
                
                foreach($files_index as $file){
                    if (!isset($file['name']) || !isset($file['size'])){
                        $this->error('Unable to read files from the bundle');
                        return false;
                    }
                    
                    if ($file['name'] == $file_name){
                        $file_content = fread($fp, $file['size']);
                        fclose($fp);
                        unlink($file_path);
                        return $file_content;
                    }else{
                        fseek($fp, $file['size']);
                    }
                }

                // Close the file
                fclose($fp);

                // Unlink the temp file
                unlink($file_path);

                // Return not found
                $this->error("{$file_path} was not found in the bundle.");
                return false;
            }
        }
        
        public function extract_file_to_file($file_name_to_extract, $file_path_to_output){
            if ($this->is_loaded){
                $file_path = $this->file_store->write_to_file($this->_file_key);
                
                if (!$file_path || file_size($file_path) == 0){
                    $this->error('Unable to open bundle');
                    return false;
                }
                
                // Open the file
                $ftp = fopen($file_path, "r");

                // Check the file opened
                if (!$fp){
                    $this->error("Unable to read the temp file {$file_path}");
                    return false;
                }

                // Extract the bundles manifest
                $bundle_manifest = fgets($fp);

                // Check we have something
                if (strlen($bundle_manifest) == 0){
                    $this->error("Unable to read the bundles manifest");
                    return false;
                }

                // Check it's json
                if (!is_json($bundle_manifest)){
                    $this->error("Bundle manifest is not valid JSON");
                    return false;
                }

                // Parse the JSON
                $files_index = json_decode($bundle_manifest, true);

                // Check the index is an array
                if (!is_array($files_index)){
                    $this->error("The file index is not valid");
                    return false;
                }
                
                foreach($files_index as $file){
                    if (!isset($file['name']) || !isset($file['size'])){
                        $this->error('Unable to read files from the bundle');
                        return false;
                    }
                    
                    if ($file['name'] == $file_name_to_extract){
                        $ofp = fopen($file_path_to_output, "r");
                        fwrite($ofp, fread($fp, $file['size']));
                        fclose($ofp);
                        fclose($fp);
                        unlink($file_path);
                        return true;
                    }else{
                        fseek($fp, $file['size']);
                    }
                }

                // Close the file
                fclose($fp);

                // Unlink the temp file
                unlink($file_path);

                // Return not found
                $this->error("{$file_path} was not found in the bundle.");
                return false;
            }
        }
        
        public function get_file_size($file_name){
            if ($this->is_loaded){
                foreach($this->file_index as $file){
                    if ($file['name'] == $file_name){
                        return $file['size'];
                    }
                }
            }
            
            $this->error("{$file_name} was not found in this bundle.");
            return false;
        }
        
    }
}