<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace adapt{

    /**
     * Description of model_bundle
     *
     * @author matt
     */
    class model_bundle extends model{
        
        const EVENT_ON_LOAD_BY_NAME_AND_VERSION = 'model_bundle.load_by_name_and_version';
        
        public function __construct($id = null, $data_source = null){
            parent::__construct('bundle', $id, $data_source);
        }
        
        public function get_version($version = "latest"){
            if (!$this->name){
                $this->error("Bundle not loaded");
                return false;
            }
            $model_version = new model_bundle_version();
            if ($version == "latest"){
                if (!$model_version->load_latest_by_bundle_name($this->name)){
                    $this->error("Unknown version");
                    return false;
                }
            }else{
                if (!$model_version->load_by_name_and_version($this->name, $version)){
                    $this->error("Unknown version");
                    return false;
                }
            }
            
            return $model_version;
        }
    }
}