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
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * The foundation class for building data sources.
     *
     * @property array $schema
     * An array containing the schema for the data source.
     * @property array $data_types
     * An array of supported data types of the data source.
     */
    abstract class data_source extends base implements interfaces\data_source{
        
        /** @ignore */
        protected $_schema;
        
        /** @ignore */
        protected $_data_types;
        
        /**
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
        /** @ignore */
        public function pget_schema(){
            return $this->_schema;
        }
        
        /** @ignore */
        public function pset_schema($schema){
            $this->_schema = $schema;
        }
        
        /** @ignore */
        public function pget_data_types(){
            return $this->_data_types;
        }
        
        /** @ignore */
        public function pset_data_types($data_types){
            $this->_data_types = $data_types;
        }
        
        /**
         * Get the number of datasets in this source
         *
         * @access public
         * @return integer
         */
        public function get_number_of_datasets(){
            return count($this->get_dataset_list());
            //return count($this->_schema['tables']);
        }
        
        /**
         * Get a list of datasets
         *
         * @access public
         * @return string[]|null
         * Returns a list of datasets. For a SQL database this will be a list of
         * tables.
         */
        public function get_dataset_list(){
            return array_keys($this->_schema);
            $tables = array();
            
            if (is_array($this->schema)){
                foreach($this->schema as $field){
                    if (!in_array($field['table_name'], $tables)) $tables[] = $field['table_name'];
                }
                
                return $tables;
            }
            
            $this->error('Schema not found');
            return null;
        }
        
        /**
         * Retrieve record count
         *
         * This is a placeholder function for child datasources
         * to implement.
         *
         * @access public
         * @param string
         * The dataset index or name.
         * @return integer
         */
        public function get_number_of_rows($dataset_index){
            return 0;
        }
        
        /**
         * Retrieve record structure
         *
         * Returns an array containing the record structure
         * for a particular dataset.
         *
         * @access public
         * @param string
         * The dataset index or name
         * @return array|null
         */
        public function get_row_structure($dataset_index){
            
            if (isset($this->_schema[$dataset_index])){
                return $this->_schema[$dataset_index];
            }else{
                $this->error("Dataset '{$dataset_index}' does not exist");
            }
            
            return null;
            
            print_r($this->_schema);
            exit(1);
            
            $dataset_index = strtolower($dataset_index);

            if (is_array($this->_schema)){
                $fields = array();
                foreach($this->_schema as $field){
                    if (strtolower($field['table_name']) == $dataset_index){
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
        }
        
        /**
         * Retrieve field structure
         *
         * Returns an array containing the record structure for a
         * particular field.
         *
         * @access public
         * @param string
         * The dataset index or name
         * @param string
         * The field name
         * @return array|null
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
        }
        
        /**
         * If the provided $field_name on $table_name is a foreign key
         * then the table and field it pertains to is returned.
         *
         * The returned array will contain two keys, 'table_name' and 'field_name'
         * when a reference is found, else returns an empty array.
         *
         * @access public
         * @param string
         * The table name of the field you wish to find the reference of.
         * @param string
         * The field name you wish to find the reference of.
         * @return array
         */
        public function get_reference($table_name, $field_name){
            $output = array();
            
            $field_name = strtolower($field_name);
            
            $schema = $this->_schema[$table_name];
            if (is_array($schema)){
                foreach($schema as $field){
                    if ($field['field_name'] == $field_name){
                        $output = array(
                            'table_name' => $field['referenced_table_name'],
                            'field_name' => $field['referenced_field_name']
                        );
                    }
                }
            }
            
            return $output;
            
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
        }
        
        /**
         * If the provided $field_name on $table_name is a primary key
         * then any relationships to this field is returned.
         *
         * The returned array of arrays will contain two keys, 'table_name' and 'field_name'
         * when a reference is found, else returns an empty array.
         *
         * @access public
         * @param string
         * The table name of the field you wish to find the reference of.
         * @param string
         * The field name you wish to find the reference of.
         * @return array
         */
        public function get_referenced_by($table_name, $field_name){
            $output = array();
            
            if (is_array($this->_schema)){
                foreach($this->_schema as $table => $fields){
                    foreach($fields as $field){
                        if ($field['referenced_table_name'] == $table && $field['referenced_field_name'] == $field_name){
                            $output[] = [
                                'table_name' => $field['table_name'],
                                'field_name' => $field['field_name']
                            ];
                        }
                    }
                }
            }
            
            return $output;
            
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
            
            return $output;
        }
        
        /**
         * Returns the relationship between two tables.
         *
         * @access public
         * @param string
         * The first table
         * @param string
         * The second table
         * @return array
         * An array containing two keys, 'field1' and 'field2'.
         * **field1** is the field from $table1 that related to **field2** from $table2
         */
        public function get_relationship($table1, $table2){
            $fields = $this->_schema[$table1];
            
            if (is_array($fields)){
                foreach($fields as $field){
                    if ($field['referenced_table_name'] == $table2){
                        return array(
                            'field1' => $field['field_name'],
                            'field2' => $field['referenced_field_name']
                        );
                    }
                }
            }
            
            $fields = $this->_schema[$table2];
            
            if (is_array($fields)){
                foreach($fields as $field){
                    if ($field['referenced_table_name'] == $table1){
                        return array(
                            'field2' => $field['field_name'],
                            'field1' => $field['referenced_field_name']
                        );
                    }
                }
            }
            
            return;
            
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
        
        /**
         * Returns the data type record for a particular $data_type
         *
         * @access public
         * @param string
         * The name or id of the data type
         * @return array
         * Array of key / value pairs describing the data type.
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
        }
        
        /**
         * Returns the data type record for the named $data_type
         * 
         * @access public
         * @param string
         * The name the data type
         * @return integer
         * The ID for the data type.
         */
        public function get_data_type_id($data_type){
            $type = $this->get_data_type($data_type);
            if ($type && is_array($type)){
                return $type['data_type_id'];
            }
            
            return null;
        }
        
        /**
         * Returns the base data type for the named $data_type.
         *
         * Data types in Adapt are flexible, the bundle **advanced_data_types**
         * introduces a number of new data types, including for example **email**.
         *
         * Because the underlying data source provider is unlikey to support a
         * data type named 'email' this method is used to reduce to the data
         * type down to a level that the data source understands. For a
         * SQL database the 'email' data type will be reduced to a varchar.
         *
         * @access string
         * The name or ID of the data type to reduce.
         * @return array
         * An array containing the base data type
         */
        public function get_base_data_type($data_type){
            $output = $this->get_data_type($data_type);
            $type = $output;
            while (!is_null($output)){
                $output = $this->get_data_type($data_type);
                if (!is_null($output)) $type = $output;
            }
            
            return $type;
        }
        
        /**
         * Retrieve record
         *
         * Placeholder function to be over-ridden by children.
         *
         * @access public
         * @param string
         * The dataset index or name
         * @param integer
         * The row offset of the first record to retrieve.
         * @param integer
         * The number of rows to retrieve.
         * @return array
         * An array of records.
         */
        public function get($dataset_index, $row_offset, $number_of_rows = 1){
            
        }
    }

}

?>