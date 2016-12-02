<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2016 Matt Bruton
 * Authored by Matt Bruton (matt@adaptframework.com)
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
        
        public function __construct($id = null, $data_source = null){
            parent::__construct("bundle_version", $id, $data_source);
        }
        
        public function load_by_name_and_version($bundle_name, $bundle_version){
            $this->initialise();
            
            if ($bundle_name && $bundle_version){
                $sql = $this->data_source->sql;
                
                $sql->select('*')
                    ->from('bundle_version')
                    ->where(
                        new sql_and(
                            new sql_cond('name', sql::EQUALS, sql::q($bundle_name)),
                            new sql_cond('version', sql::EQUALS, sql::q($bundle_version)),
                            new sql_cond('date_deleted', sql::IS, new sql_null())
                        )
                    );

                $results = $sql->execute(0)->results();
                
                if (count($results) == 1){
                    $this->trigger(self::EVENT_ON_LOAD_BY_NAME_AND_VERSION);
                    return $this->load_by_data($results[0]);
                }elseif(count($results) == 0){
                    $this->error("Unable to find a record");
                }elseif(count($results) > 1){
                    $this->error(count($results) . " records found, expecting 1.");
                }
            }
            
            $this->initialise();
            return false;
        }
        
        
    }

}

?>