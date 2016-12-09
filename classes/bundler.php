<?php

namespace adapt{
    
    class bundler extends base{
        
        public function bundle($bundle_path, $output_path){
            if (!$bundle_path){
                $this->error("Bundle path is required");
                return false;
            }
            
            if (!$output){
                $this->error("Output path is required");
                return false;
            }
            
            $manifest = array();
            $this->process_directory($argv[1], "/", $manifest);

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
        }
        
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
