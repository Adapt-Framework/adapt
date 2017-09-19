<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
 * http://www.adaptframework.com
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

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class model_bundle_version extends model{
        
        const EVENT_ON_LOAD_BY_NAME_AND_VERSION = 'model_bundle_version.load_by_name_and_version';
        const EVENT_ON_LOAD_LATEST_BY_BUNDLE_NAME = 'model_bundle_version.load_by_latest_bundle_name';
        
        const STATUS_AVAILABLE = "Available";
        const STATUS_INSTALLING = "Installing";
        const STATUS_INSTALLED = "Installed";
        
        public function __construct($id = null, $data_source = null){
            parent::__construct("bundle_version", $id, $data_source);
        }
        
        public function pget_is_actively_installing(){
            $modified = $this->date_modified;
            $max_install_time = intval($this->setting('adapt.max_bundle_install_time')) ?: 3;
            
            if (!$modified){
                return false;
            }
            $date = new date($date_modified);
            $date->goto_minutes($max_install_time);
            return $date->is_future(true);
        }
        
        public function load_latest_by_bundle_name($bundle_name){
            $this->initialise();
            
            if (!$bundle_name){
                $this->error("Bundle name required");
                return false;
            }
            
            $bundle = new model_bundle();
            if (!$bundle->load_by_name($bundle_name)){
                $this->error($bundle->errors(true));
                return false;
            }
            
            $sql = $this->data_source->sql;
            
            $sql->select('bv.version')
                ->from('bundle_version', 'bv')
                ->join('bundle', 'b',
                    new sql_and(
                        new sql_cond('b.bundle_id', sql::EQUALS, 'bv.bundle_id'),
                        new sql_cond('b.date_deleted', sql::IS, sql::NULL)
                    )
                )
                ->where(
                    new sql_and(
                        new sql_cond('bv.bundle_id', sql::EQUALS, $bundle->bundle_id),
                        new sql_cond('bv.date_deleted', sql::IS, sql::NULL)
                    )
                );
            
            $results = $sql->execute(0)->results();
            
            if (count($results) == 0){
                $this->error("Unable to load record");
                return false;
            }
            
            $versions = [];
            foreach($results as $result){
                $versions[] = $result['version'];
            }
            
            $this->trigger(self::EVENT_ON_LOAD_BY_NAME_AND_VERSION);
            return $this->load_by_name_and_version($bundle_name, bundles::get_newest_version($versions));
            
        }
        
        public function load_by_name_and_version($bundle_name, $bundle_version){
            $this->initialise();
            
            if (!$bundle_name){
                $this->error("Bundle name required");
                return false;
            }
            
            if (!$bundle_version){
                $this->error("Bundle version required");
                return false;
            }
            
            $bundle = new model_bundle();
            if (!$bundle->load_by_name($bundle_name)){
                $this->error($bundle->errors(true));
                return false;
            }
            
            $sql = $this->data_source->sql;
            
            $sql->select('bv.*')
                ->from('bundle_version', 'bv')
                ->join('bundle', 'b',
                    new sql_and(
                        new sql_cond('b.bundle_id', sql::EQUALS, 'bv.bundle_id'),
                        new sql_cond('b.date_deleted', sql::IS, sql::NULL)
                    )
                )
                ->where(
                    new sql_and(
                        new sql_cond('bv.bundle_id', sql::EQUALS, $bundle->bundle_id),
                        new sql_cond('bv.version', sql::EQUALS, sql::q($bundle_version)),
                        new sql_cond('bv.date_deleted', sql::IS, sql::NULL)
                    )
                );
            
            $results = $sql->execute(0)->results();
            
            if (count($results) != 1){
                $this->error("Unable to load record");
                return false;
            }
            
            $this->trigger(self::EVENT_ON_LOAD_BY_NAME_AND_VERSION);
            return $this->load_by_data($results[0]);
        }
        
        
    }

}
