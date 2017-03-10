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
    defined('ADAPT_STARTED') or die();
    
    class model_field extends model{
        
        const EVENT_ON_LOAD_BY_TABLE_NAME_AND_FIELD_NAME = 'model_field.load_by_table_name_and_field_name';
        
        public function __construct($id = null, $data_source = null){
            parent::__construct('field', $id, $data_source);
        }
        
        public function load_by_table_name_and_field_name($table_name, $field_name){
            $this->initialise();
            
            if ($table_name && $field_name){
                $sql = $this->data_source->sql;
                
                $sql->select('*')
                    ->from('field')
                    ->where(
                        new sql_and(
                            new sql_cond('table_name', sql::EQUALS, sql::q($table_name)),
                            new sql_cond('field_name', sql::EQUALS, sql::q($field_name)),
                            new sql_cond('date_deleted', sql::IS, new sql_null())
                        )
                    );
                
                $results = $sql->execute(0)->results();
                
                if (count($results) == 1){
                    $this->trigger(self::EVENT_ON_LOAD_BY_TABLE_NAME_AND_FIELD_NAME);
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
        
        public function save(){
            if (parent::save()){
                // Update the datasource schema for this table
                $schema = $this->data_source->schema;
                if (!is_array($schema[$this->_data['table_name']])){
                    $schema[$this->_data['table_name']] = [];
                }
                
                $table_schema = $schema[$this->_data['table_name']];
                $new_table_schema = [];
                
                $appended = false;
                foreach($table_schema as $field_schema){
                    if ($field_schema['field_name'] == $this->field_name){
                        $new_table_schema[] = $this->to_hash()['field'];
                        $appended = true;
                    }else{
                        $new_table_schema[] = $field_schema;
                    }
                }
                
                if (!$appended) {
                    $new_table_schema[] = $this->to_hash()['field'];
                }
                
                $schema[$this->_data['table_name']] = $new_table_schema;
                
                $this->data_source->schema = $schema;
                
                return true;
            }
            
            return false;
        }
        
    }
    
}