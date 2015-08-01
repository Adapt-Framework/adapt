<?php

/*
 * The MIT License (MIT)
 *   
 * Copyright (c) 2015 Adapt Framework (www.adaptframework.com)
 * Authored by Matt Bruton (matt@adaptframework.com)
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

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    abstract class data_source extends base implements interfaces\data_source{
        
        protected $_schema;
        protected $_data_types;
        
        /*
         * Constructor
         */
        public function __construct(){
            parent::__construct();
            /*$this->_schema = array(
                'tables' => array(),
                'references' => array(),
                'data_types' => array()
            );*/
            $this->_schema = array();
            $this->_data_types = array();
        }
        
        /*
         * Properties
         */
        public function aget_schema(){
            return $this->_schema;
        }
        
        public function aset_schema($schema){
            $this->_schema = $schema;
        }
        
        public function aget_data_types(){
            return $this->_data_types;
        }
        
        public function aset_data_types($data_types){
            $this->_data_types = $data_types;
        }
        
        /*
         * Get the number of datasets in this source
         */
        public function get_number_of_datasets(){
            return count($this->get_dataset_list());
            //return count($this->_schema['tables']);
        }
        
        /*
         * Get a list of datasets
         */
        public function get_dataset_list(){
            $tables = array();
            
            if (is_array($this->schema)){
                foreach($this->schema as $field){
                    if (!in_array($field['table_name'], $tables)) $tables[] = $field['table_name'];
                }
                
                return $tables;
            }
            
            $this->error('Schema not found');
            return null;
            
            //return array_keys($this->schema['tables']);
        }
        
        /*
         * Retrieve record count
         */
        public function get_number_of_rows($dataset_index){
            
        }
        
        /*
         * Retrieve record structure
         */
        public function get_row_structure($dataset_index){
            
            if (is_array($this->_schema)){
                $fields = array();
                foreach($this->_schema as $field){
                    if (strtolower($field['table_name']) == strtolower($dataset_index)){
                        $fields[] = $field;
                    }
                }
                
                if (count($fields)){
                    return $fields;
                }else{
                    $this->error("Dataset '{$dataset_index}' does not exist");
                    return null;
                }
            }
            
            $this->error('Schema not found');
            return null;
            
            //if (is_array($this->_schema['tables'][$dataset_index])){
            //    return $this->_schema['tables'][$dataset_index];
            //}
            //
            //return null;
        }
        
        /*
         * Retrieve field structure
         */
        public function get_field_structure($dataset_index, $field_name){
            $row = $this->get_row_structure($dataset_index);
            
            if (is_array($row)){
                foreach($row as $field){
                    if (strtolower($field['field_name']) == strtolower($field_name)){
                        return $field;
                    }
                }
            }
            
            return null;
            //$row = $this->get_row_structure($dataset_index);
            //
            //if (!is_null($row)){
            //    if (is_array($row[$field_name])){
            //        return $row[$field_name];
            //    }
            //}
            //return null;
        }
        
        /*
         * Reference functions
         */
        public function get_reference($table_name, $field_name){
            $output = array();
            
            if (is_array($this->_schema)){
                foreach($this->_schema as $field){
                    if (strtolower($field['table_name']) == strtolower($table_name)){
                        if (strtolower($field['field_name']) == strtolower($field_name)){
                            $output = array(
                                'table_name' => $field['referenced_table_name'],
                                'field_name' => $field['referenced_field_name']
                            );
                        }
                    }
                }
            }
            
            if (!isset($output['table_name'])) $output = array();
            
            return $output;
            //$output = array();
            //$schema = $this->schema;
            //
            //if (isset($schema['references'])){
            //    foreach($schema['references'] as $ref){
            //        if ($ref['table_name'] == $table_name && $ref['field_name'] == $field_name){
            //            $output = array(
            //                'table_name' => $ref['referenced_table_name'],
            //                'field_name' => $ref['referenced_field_name']
            //            );
            //        }
            //    }
            //}
            //
            //return $output;
        }
        
        public function get_referenced_by($table_name, $field_name){
            $output = array();
            
            if (is_array($this->_schema)){
                foreach($this->_schema as $field){
                    if (strtolower($field['referenced_table_name']) == strtolower($table_name)){
                        if (strtolower($field['referenced_field_name']) == strtolower($field_name)){
                            $output[] = array(
                                'table_name' => $field['table_name'],
                                'field_name' => $field['field_name']
                            );
                        }
                    }
                }
            }
            
            //if (!isset($output['table_name'])) $output = array();
            
            return $output;
            
            //$output = array();
            //$schema = $this->schema;
            //
            //if (isset($schema['references'])){
            //    foreach($schema['references'] as $ref){
            //        if ($ref['referenced_table_name'] == $table_name && $ref['referenced_field_name'] == $field_name){
            //            $output = array(
            //                'table_name' => $ref['table_name'],
            //                'field_name' => $ref['field_name']
            //            );
            //        }
            //    }
            //}
            //
            //return $output;
        }
        
        public function get_relationship($table1, $table2){
            $schema = $this->_schema;
            
            if (is_array($schema)){
                foreach($this->_schema as $field){
                    if (strtolower($field['table_name']) == strtolower($table1)){
                        if (strtolower($field['referenced_table_name']) == strtolower($table2)){
                            return array(
                                'field1' => $field['field_name'],
                                'field2' => $field['referenced_field_name']
                            );
                        }
                    }elseif (strtolower($field['table_name']) == strtolower($table2)){
                        if (strtolower($field['referenced_table_name']) == strtolower($table1)){
                            return array(
                                'field2' => $field['field_name'],
                                'field1' => $field['referenced_field_name']
                            );
                        }
                    }
                }
            }
            
            return null;
        }
        
        /*
         * Retrieve data types
         */
        public function get_data_type($data_type){
            if (is_array($this->data_types)){
                foreach($this->data_types as $type){
                    if (is_numeric($data_type)){
                        if ($type['data_type_id'] == $data_type){
                            return $type;
                        }
                    }else{
                        if (strtolower($type['name']) == strtolower($data_type)){
                            return $type;
                        }
                    }
                }
            }
            
            return null;
            //if (is_array($this->_schema) && isset($this->_schema['data_types']) && isset($this->_schema['data_types'][$data_type])){
            //    return $this->_schema['data_types'][$data_type];
            //}
            //
            //return null;
        }
        
        public function get_data_type_id($data_type){
            $type = $this->get_data_type($data_type);
            if ($type && is_array($type)){
                return $type['data_type_id'];
            }
            
            return null;
        }
        
        public function get_base_data_type($data_type){
            $output = $this->get_data_type($data_type);
            $type = $output;
            while (!is_null($output)){
                $output = $this->get_data_type($data_type);
                if (!is_null($output)) $type = $output;
            }
            
            return $type;
            //$data_type = $this->get_data_type($data_type);
            //if (isset($data_type)){
            //    return $this->get_data_type($data_type['based_on']);
            //}
            //
            //return null;
        }
        
        
        /*
         * Retrieve record
         */
        public function get($dataset_index, $row_offset, $number_of_rows = 1){
            
        }
    }

}

?>