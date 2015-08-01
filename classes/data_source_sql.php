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
    
    abstract class data_source_sql extends data_source implements interfaces\data_source_sql{
        
        const EVENT_HOST_CONNECT = 'adapt.host_connect';
        const EVENT_HOST_DISCONNECT = 'adapt.host_disconnect';
        const EVENT_QUERY = 'adapt.query';
        
        const FETCH_ASSOC = 1;
        const FETCH_ARRAY = 2;
        const FETCH_OBJECT = 3;
        const FETCH_ALL_ASSOC = 4;
        const FETCH_ALL_ARRAY = 5;
        const FETCH_ALL_OBJECT = 6;
        
        protected $_hosts;
        protected $_read_host;
        protected $_write_host;
        
        /*
         * Constructor
         */
        public function __construct($host = null, $username = null, $password = null, $schema = null, $read_only = false){
            parent::__construct();
            
            /* Kill the schema */
            $this->_schema = null;
            $this->_data_types = null;
            
            /* Set the hosts */
            $this->_hosts = array();
            
            if (!is_null($host) && !is_null($username) && !is_null($password) && !is_null($schema)){
                $this->add_host($host, $username, $password, $schema, $read_only);
                $this->load_schema();
            }
            
        }
        
        /*
         * Properties
         */
        public function aget_schema(){
            if (!is_array($this->_schema)){
                $this->load_schema();
            }
            
            return parent::aget_schema();
        }
        
        public function aget_data_types(){
            if (!is_array($this->_data_types)){
                $this->load_schema();
            }
            
            return parent::aget_data_types();
        }
        
        public function aget_sql(){
            return new sql(null, $this);
        }
        
        public function sql($statement = null){
            return new sql($statement, $this);
        }
        
        /*
         * Retrive number of rows
         */
        public function get_number_of_rows($table_name){
            $table_schema = $this->get_row_structure($table_name);
            if (!is_null($table_schema)){
                $sql = new sql(null, $this);
                $sql->select(new sql('count(*)'), 'c')->from($table_name);
                if (isset($table_schema['date_deleted'])){
                    $sql->where(new sql_condition('date_deleted', 'is not', new sql('null')));
                }
                
                $results = $sql->execute()->results();
                return $results[0]['c'];
            }
        }
        
        /*
         * Retrieve a record
         */
        public function get($table_name, $row_offset, $number_of_rows = 1){
            $table_schema = $this->get_row_structure($table_name);
            if (!is_null($table_schema)){
                $sql = new sql(null, $this);
                $sql->select(new sql('*'))->from($table_name)->limit($number_of_rows, $row_offset);
                //if (isset($table_schema['date_deleted'])){
                if (!is_null($this->get_field_structure($table_name, 'date_deleted'))){
                    $sql->where(new sql_condition('date_deleted', 'is not', new sql('null')));
                }
                
                return $sql->execute()->results();
            }
        }
        
        /*
         * Get key fields
         */
        public function get_primary_keys($table_name){
            $keys = array();
            
            $fields = $this->get_row_structure($table_name);
            foreach($fields as $field){
                if ($field['primary_key'] == 'Yes') $keys[] = $field['field_name'];
            }
            
            return $keys;
            //$keys = array();
            //$structure = $this->get_row_structure($table_name);
            //
            //foreach($structure as $field_name => $field){
            //    if (isset($field['primary_key']) && $field['primary_key'] == 'Yes'){
            //        $keys[] = $field_name;
            //    }
            //}
            //
            //return $keys;
        }
        
        /*
         * SQL Execution
         */
        public function write($sql){
            return $this->query($sql, true);
        }
        
        public function read($sql){
            return $this->query($sql);
        }
        
        public function query($sql, $write = false){
            
        }
        
        public function fetch($statement_handle, $fetch_type = self::FETCH_ASSOC){
            
        }
        
        public function last_insert_id(){
            
        }
        
        /*
         * Host management
         */
        public function add_host($host, $username, $password, $schema, $read_only = false){
            $this->_hosts[] = array(
                'host' => $host,
                'username' => $username,
                'password' => $password,
                'schema' => $schema,
                'read_only' => $read_only
            );
        }
        
        public function connect($host){
            
        }
        
        public function disconnect($host){
            
        }
        
        public function get_host($writable = false){
            if ($writable){
                if (!is_null($this->_write_host)){
                    return $this->_write_host;
                }else{
                    $hosts = $this->_hosts;
                    shuffle($hosts);
                    foreach($hosts as $host){
                        if ($host['read_only'] === false){
                            if ($host['handle'] = $this->connect($host)){
                                $this->_write_host = $host;
                                return $this->_write_host;
                            }
                        }
                    }
                }
            }else{
                if (!is_null($this->_read_host)){
                    return $this->_read_host;
                }else{
                    $hosts = $this->_hosts;
                    shuffle($hosts);
                    foreach($hosts as $host){
                        if ($host['handle'] = $this->connect($host)){
                            $this->_read_host = $host;
                            return $this->_read_host;
                        }
                    }
                }
            }
            
            return null;
        }
        
        /*
         * SQL Rendering
         */
        public function render_sql(\frameworks\adapt\sql $sql){
            
        }
        
        /*
         * Escaping values
         */
        public function escape($string){
            /*
             * This function should be overidden by the child
             * to ensure proper escaping on the target plaform.
             * This is only provided as a fall back for databases
             * that have no native escape function.
             */
            return addslashes($string);
        }
        
        /*
         * Data validation
         */
        public function validate($table_name, $field_name, $value){
            //print new html_pre("IN: Validating {$table_name}.{$field_name} against '{$value}'");
            /*
             * This doesn't deal with dependencies or mandatory
             * groups.  This must be handled by the model
             */
            $valid = true;
            
            $field = $this->get_field_structure($table_name, $field_name);
            if (is_array($field) && is_assoc($field)){
                $data_type = $this->get_data_type($field['data_type_id']);
                if (is_array($data_type)){
                    $validator = $data_type['validator'];
                    $formatter = $data_type['formatter'];
                    $unformatter = $data_type['unformatter'];
                    $datetime_format = $data_type['datetime_format'];
                    $max_length = $data_type['max_length'];
                    $nullable = strtolower($field['nullable']) == 'yes' ? true : false;
                    
                    if (!is_object($value)){ //Prevents SQL objects from failing validation
                        if (!is_null($value) && $value != ''){
                            //if (!is_null($unformatter)){
                            //    $value = $this->sanitize->unformat($unformatter, $value);
                            //}
                            
                            if (!is_null($max_length) && is_integer($max_length) && strlen($value) > $max_length){
                                $this->error("Maximum field size is {$max_size}");
                                $valid = false;
                            }
                            
                            if (!is_null($validator)){
                                if (!$this->sanitize->validate($validator, $value)){
                                    $this->error("The value of '{$value}' for {$table_name}.{$field_name} is not valid");
                                    $valid = false;
                                }
                            }
                        }else{
                            if (!$nullable){
                                $this->error("The value for {$table_name}.{$field_name} cannot be null");
                                $valid = false;
                            }
                        }
                    }
                    
                    //TODO: Check allowed values
                }
            }else{
                $this->error("Field {$table_name}.{$field_name} not found");
            }
            
            //if ($valid){
            //    print new html_pre("Validation passed");
            //}else{
            //    print new html_pre("Validation FAILED");
            //    print new html_pre(print_r($this->errors(true)));
            //}
            
            return $valid;
        }
        
        /*
         * Data presentation
         */
        public function format($table_name, $field_name, $value){
            $field = $this->get_field_structure($table_name, $field_name);
            
            if (is_array($field) && is_assoc($field)){
                $data_type = $this->get_data_type($field['data_type_id']);
                if (is_array($data_type)){
                    $formatter = $data_type['formatter'];
                    //$date_format = $data_type['datetime_format'];
                    //
                    //if (!is_null($date_format)){
                    //    $base = $this->get_data_type($data_type['based_on_data_type']);
                    //    if (is_array($base)  && $base['date_type_id'] != $data_type['data_type_id']){
                    //        $base_dateformat = $base['datetime_format'];
                    //        if (!is_null($base_dateformat)){
                    //            $date = new date();
                    //            $date->set_date($value, $base_dateformat);
                    //            $value = $date->date($date_format);
                    //        }
                    //    }else{
                    //         /* Lets format using adapt.default_xxx_format */
                    //        $default_data_type = null;
                    //        switch($data_type['name']){
                    //        case 'date':
                    //        case 'time':
                    //        case 'datetime':
                    //            $default_data_type = $this->setting("adapt.default_{$data_type['name']}_format");
                    //            break;
                    //        }
                    //        
                    //        if (!is_null($default_data_type)){
                    //            $default_data_type = $this->get_data_type($default_data_type);
                    //            $date = new date();
                    //            $date->set_date($value, $data_type['datetime_format']);
                    //            $value = $date->date($default_data_type['datetime_format']);
                    //        }
                    //    }
                    //    
                    //}elseif (!is_null($formatter)){
                        $value = $this->sanitize->format($formatter, $value);
                    //}
                }
            }
            
            return $value;
        }
        
        public function unformat($table_name, $field_name, $value){
            if (is_object($value) && $value instanceof sql) return $value;
            //if ($table_name == 'vacancy'){
            //    print new html_pre("data_source_sql.unformat: {$field_name} set to '{$value}'");
            //}
            $field = $this->get_field_structure($table_name, $field_name);
            //if ($table_name == 'vacancy'){
            //    print new html_pre(print_r($field, true));
            //}
            if (is_array($field) && is_assoc($field)){
                $data_type = $this->get_data_type($field['data_type_id']);
                if (is_array($data_type)){
                    $unformatter = $data_type['unformatter'];
                    
                    
                    //$date_format = $data_type['datetime_format'];
                    //
                    //if (!is_null($date_format)){
                    //    $base = $this->get_data_type($data_type['based_on_data_type']);
                    //    if (is_array($base) && $base['data_type_id'] != $data_type['data_type_id']){
                    //        $base_dateformat = $base['datetime_format'];
                    //        if (!is_null($base_dateformat)){
                    //            $date = new date();
                    //            $date->set_date($value, $date_format);
                    //            $value = $date->date($base_dateformat);
                    //        }
                    //    }else{
                    //        /* Lets unformat using adapt.default_xxx_format */
                    //        $default_data_type = null;
                    //        switch($data_type['name']){
                    //        case 'date':
                    //        case 'time':
                    //        case 'datetime':
                    //            $default_data_type = $this->setting("adapt.default_{$data_type['name']}_format");
                    //            break;
                    //        }
                    //        
                    //        if (!is_null($default_data_type)){
                    //            $default_data_type = $this->get_data_type($default_data_type);
                    //            $date = new date();
                    //            $date->set_date($value, $default_data_type['datetime_format']);
                    //            $value = $date->date($data_type['datetime_format']);
                    //        }
                    //    }
                    //}elseif (!is_null($unformatter)){
                        $value = $this->sanitize->unformat($unformatter, $value);
                    //}
                }
            }
            
            return $value;
            //
            //$structure = $this->get_field_structure($table_name, $field_name);
            //if (is_array($structure) && isset($structure['data_type'])){
            //    if (isset($this->schema['data_types'][$structure['data_type']])){
            //        $data_type = $this->schema['data_types'][$structure['data_type']];
            //        $unformatter = $data_type['unformatter'];
            //        
            //        if (!is_null($unformatter)){
            //            $value = $this->sanitize->unformat($unformatter, $value);
            //        }
            //    }
            //}
            //
            //return $value;
        }
        
        /*
         * Data types
         */
        public function convert_data_type($type, $signed = true, $zero_fill = false){
            
        }
        
        /*
         * Schema loading
         */
        protected function load_schema(){
            $this->schema = $this->sql
                ->select(new sql('*'))
                ->from('field')
                ->where(new sql_condition(new sql('date_deleted'), 'is', new sql('null')))
                ->execute()
                ->results();
            
            $this->data_types = $this->sql
                ->select(new sql('*'))
                ->from('data_type')
                ->where(new sql_condition(new sql('date_deleted'), 'is', new sql('null')))
                ->execute()
                ->results();
            
            
            return;
            ///*
            // * Cretae a new sql object
            // */
            //$sql = new \adapt\sql(null, $this);
            //
            //
            ///*
            // * Create a new blank schema
            // */
            //$schema = array(
            //    'tables' => array(),
            //    'references' => array(),
            //    'data_types' => array()
            //);
            //
            ///*
            // * Get the table structures
            // */
            //$results = $sql
            //    ->select(new sql('*'))
            //    ->from('adapt_field')
            //    ->where(
            //        new sql_condition(new sql('date_deleted'), 'is', new sql('null'))
            //    )
            //    ->execute()
            //    ->results();
            //
            //foreach($results as $r){
            //    $key = $r['table_name'];
            //    $field = $r['field_name'];
            //    if (!isset($schema['tables'][$key])) $schema['tables'][$key] = array();
            //    
            //    unset($r['table_name']);
            //    unset($r['field_name']);
            //    
            //    $schema['tables'][$key][$field] = $r;
            //}
            //
            ///*
            // * Get the references
            // */
            //$schema['references'] = $sql
            //    ->select(new sql('*'))
            //    ->from('adapt_reference')
            //    ->where(
            //        new sql_condition(new sql('date_deleted'), 'is', new sql('null'))
            //    )
            //    ->execute()
            //    ->results();
            //
            ///*
            // * Get data types
            // */
            //$results = $sql
            //    ->select(new sql('*'))
            //    ->from('adapt_data_type')
            //    ->where(
            //        new sql_condition(new sql('date_deleted'), 'is', new sql('null'))
            //    )
            //    ->execute()
            //    ->results();
            //
            //foreach($results as $result){
            //    $key = $result['name'];
            //    //unset($result['name']);
            //    $schema['data_types'][$key] = $result;
            //}
            //
            ///*
            // * Set the schema
            // */
            //$this->_schema = $schema;
        }
    }

}

?>