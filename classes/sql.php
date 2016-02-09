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

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class sql extends base{
        
        const ON_DELETE_SET_NULL = "SET NULL";
        const ON_DELETE_CASCADE = "CASCADE";
        
        const JOIN = "JOIN";
        const LEFT_JOIN = "LEFT JOIN";
        const RIGHT_JOIN = "RIGHT JOIN";
        const INNER_JOIN = "INNER JOIN";
        const OUTER_JOIN = "OUTER JOIN";
        
        const LOGICAL_AND = "&&";
        const BITWISE_AND = "&";
        const INVERT_BITS = "~";
        const BITWISE_OR = "|";
        const BITWISE_XOR = "^";
        const DIVIDE = "/";
        const NULL_SAFE_EQUALS = "<=>";
        const EQUALS = "=";
        const GREATER_THAN_OR_EQUALS = ">=";
        const GREATER_THAN = ">";
        const IS_NOT_NULL = "IS NOT NULL";
        const IS_NULL = "IS NULL";
        const IS_NOT = "IS NOT";
        const IS = "IS";
        const LEFT_SHIFT = "<<";
        const LESS_THAN_OR_EQUALS = "<=";
        const LESS_THAN = "<";
        const LIKE = "LIKE";
        const MINUS = "-";
        const MOD = "%";
        const NOT_EQUALS = "!=";
        const NOT_LIKE = "NOT LIKE";
        const NOT = "!";
        const LOGICAL_OR = "||";
        const ADD = "+";
        const RIGHT_SHIFT = ">>";
        const MULTIPLICATION = "*";
        const LOGICAL_XOR = "XOR";
        
        protected $_data_source;
        protected $_is_write = false;
        protected $_has_written = false;
        protected $_results = array();
        
        protected $_sql_statement = null;
        protected $_select_fields = array();
        protected $_is_distinct = false;
        protected $_from = array();
        protected $_join_conditions = array();
        protected $_where_conditions = array();
        protected $_having_conditions = array();
        protected $_grouping = array();
        protected $_ordering = array();
        protected $_limit = null;
        protected $_limit_offset = null;
        protected $_create_database = null;
        protected $_create_table = null;
        protected $_fields = array();
        protected $_indexes = array();
        protected $_primary_keys = array();
        protected $_foreign_keys = array();
        protected $_insert_into = array();
        protected $_values = array();
        protected $_update = array();
        protected $_set = array();
        protected $_alter_table = null;
        protected $_delete_from = array();
        protected $_drop_database = null;
        protected $_drop_table = null;
        
        protected $_functions = array();
        protected $_conditions = array();
        
        /*
         * Constructor
         */
        public function __construct($sql = null, $data_source = null){
            parent::__construct();
            
            $class_name = array_pop(explode("\\", get_class($this)));
            
            //print "<h3>" . $class_name . "</h3>";
            //print "<pre>" . print_r(func_get_args()) . "</pre>";
            
            if ($class_name == "sql"){
                if (!is_null($data_source)){
                    $this->_data_source = $data_source;
                }
                
                if (!is_null($sql)){
                    $this->_sql_statement = $sql;
                }
            }else{
                $params = func_get_args();
                $params = array_reverse($params);
                $function_name = array_pop($params);
                $params = array_reverse($params);
                
                $allowed_functions = array(
                    /* conditions */
                    'condition', 'cond', 'if',
                    /* Logical operators */
                    'and', 'or', 'between',
                    /* String Functions */
                    'ascii', 'char', 'concat', 'format', 'length', 'lower',
                    'ltrim', 'replace', 'reverse', 'rtrim', 'substr', 'trim',
                    'upper',
                    /* Numeric functions */
                    'abs', 'acos', 'asin', 'atan', 'atan2', 'ceil', 'cos',
                    'exp', 'floor', 'power', 'round', 'sign', 'sin', 'tan',
                    /* Datetime functions */
                    'current_date', 'current_time', 'current_timestamp',
                    'now',
                    /* Other key words */
                    'null', 'true', 'false'
                );
                
                
                if (in_array($function_name, $allowed_functions)){
                    $this->_functions[$function_name] = $params;
                }
                
                /*if (method_exists($this, $function_name)){
                    $params = array_reverse($params);
                    array_pop($params);
                    $params = array_reverse($params);
                    
                    $this->$function_name($params);
                    
                }elseif(method_exists($this, "logical_" . $function_name)){
                    
                    $function_name = "logical_" . $function_name;
                    
                    $params = array_reverse($params);
                    array_pop($params);
                    $params = array_reverse($params);
                    
                    $this->$function_name($params);
                }*/
            }
        }
        
        public static function q($string){
            $adapt = $GLOBALS['adapt'];
            $string = "\"" . $adapt->data_source->escape($string) . "\"";
            
            return $string;
        }
        
        /*
         * Dynamic functions
         */
        public function __toString(){
            $sql = $this->render();
            if (is_null($sql)){
                return "";
            }else{
                return $sql;
            }
        }
        
        /*
         * Properties
         */
        public function pget_data_source(){
            if (isset($this->_data_source) && $this->_data_source instanceof data_source_sql){
                return $this->_data_source;
            }else{
                return parent::pget_data_source();
            }
        }
        
        public function pget_statement(){
            return $this->_sql_statement;
        }
        
        public function pget_select_fields(){
            return $this->_select_fields;
        }
        
        public function pget_is_distinct(){
            return $this->_is_distinct();
        }
        
        public function pget_from_fields(){
            return $this->_from;
        }
        
        public function pget_join_conditions(){
            return $this->_join_conditions;
        }
        
        public function pget_where_conditions(){
            return $this->_where_conditions;
        }
        
        public function pget_grouping(){
            return $this->_grouping;
        }
        
        public function pget_having_conditions(){
            return $this->_having_conditions;
        }
        
        public function pget_ordering(){
            return $this->_ordering;
        }
        
        public function pget_limit_count(){
            return $this->_limit;
        }
        
        public function pget_limit_offset(){
            return $this->_limit_offset;
        }
        
        public function pget_create_database_name(){
            return $this->_create_database;
        }
        
        public function pget_create_table_name(){
            return $this->_create_table;
        }
        
        public function pget_create_table_fields(){
            return $this->_fields;
        }
        
        public function pget_alter_table_name(){
            return $this->_alter_table;
        }
        
        public function pget_alter_table_fields(){
            return $this->_fields;
        }
        
        public function pget_indexes(){
            return $this->_indexes;
        }
        
        public function pget_primary_keys(){
            return $this->_primary_keys;
        }
        
        public function pget_foreign_keys(){
            return $this->_foreign_keys;
        }
        
        public function pget_insert_into_table_name(){
            if (isset($this->_insert_into['table_name'])){
                return $this->_insert_into['table_name'];
            }
            
            return null;
        }
        
        public function pget_insert_into_fields(){
            if (isset($this->_insert_into['fields'])){
                return $this->_insert_into['fields'];
            }
            
            return array();
        }
        
        public function pget_insert_into_values(){
            return $this->_values;
        }
        
        public function pget_update_tables(){
            return $this->_update;
        }
        
        public function pget_delete_from_tables(){
            return $this->_delete_from;
        }
        
        public function pget_set(){
            return $this->_set;
        }
        
        public function pget_functions(){
            return $this->_functions;
        }
        
        public function pget_conditions(){
            return $this->_conditions;
        }
        
        public function pget_drop_database_name(){
            return $this->_drop_database;
        }
        
        public function pget_drop_table_name(){
            return $this->_drop_table;
        }
        
        /*
         * Adding additional params to functions
         * or logic operators
         */
        public function logical_add($item){
            if (count($this->_functions)){
                $names = array_keys($this->_functions);
                if (is_array($this->_functions[$names[0]])){
                    $this->_functions[$names[0]][] = $item;
                }
            }
        }
        
        
        /*
         * Select, update, delete
         */
        public function select(){
            $args = func_get_args();
            
            foreach($args as $arg){
                if (is_array($arg) && is_assoc($arg)){
                    foreach($arg as $alias => $value){
                        $this->_select_fields[] = array(
                            'alias' => $alias,
                            'value' => $value
                        );
                    }
                    
                }elseif(is_array($arg)){
                    foreach($arg as $a) $this->select($a);
                }elseif($arg instanceof sql || is_string($arg) || is_numeric($arg)){
                    $this->_select_fields[] = array('value' => $arg);
                }else{
                    /* Unknown data type */
                    $this->error('Unable to add fields to the select statement, unknown data type: ' . print_r($arg, true));
                }
            }
            
            //foreach(func_get_args() as $arg){
            //    if (is_array($arg)){
            //        if (is_assoc($arg)){
            //            foreach($arg as $alias => $value){
            //                if (is_integer($alias)) $alias = $value;
            //                $this->_select_fields[$alias] = $value;
            //            }
            //        }else{
            //            foreach($arg as $a){
            //                $this->_select_fields[$a] = $a;
            //            }
            //        }
            //    }else{
            //        if (is_string($arg)){
            //            $this->_select_fields[$arg] = $arg;
            //        }else{
            //            $this->_select_fields[] = $arg;
            //        }
            //    }
            //}
            return $this;
        }
        
        public function select_distinct(){
            $this->_is_distinct = true;
            return call_user_func_array(array($this, 'select'), func_get_args());
        }
        
        public function from($table, $alias = ""){
            if ($alias == ""){
                $this->_from = array($table);
            }else{
                $this->_from = array($alias => $table);
            }
            
            return $this;
        }
        
        public function join($table, $alias, $condition, $type = self::JOIN){
            $this->_join_conditions[] = array(
                'table' => $table,
                'alias' => $alias,
                'condition' => $condition,
                'type' => $type
            );
            
            return $this;
        }
        
        public function left_join($table, $alias, $condition){
            return $this->join($table, $alias, $condition, self::LEFT_JOIN);
        }
        
        public function right_join($table, $alias, $condition){
            return $this->join($table, $alias, $condition, self::RIGHT_JOIN);
        }
        
        public function inner_join($table, $alias, $condition){
            return $this->join($table, $alias, $condition, self::INNER_JOIN);
        }
        
        public function outer_join($table, $alias, $condition){
            return $this->join($table, $alias, $condition, self::OUTER_JOIN);
        }
        
        public function where(){
            foreach(func_get_args() as $arg){
                if (is_array($arg)){
                    foreach($arg as $a) $this->_where_conditions[] = $a;
                }else{
                    $this->_where_conditions[] = $arg;
                }
            }
            
            return $this;
        }
        
        public function group_by($field, $ascending = true, $with_rollup = false){
            $this->_grouping[] = array(
                'field' => $field,
                'ascending' => $ascending,
                'with_rollup' => $with_rollup
            );
            
            return $this;
        }
        
        public function having(){
            foreach(func_get_args() as $arg){
                if (is_array($arg)){
                    foreach($arg as $a) $this->_having_conditions[] = $a;
                }else{
                    $this->_having_conditions[] = $arg;
                }
            }
            
            return $this;
        }
        
        public function order_by($field, $ascending = true){
            $this->_ordering[] = array(
                'field' => $field,
                'ascending' => $ascending
            );
            
            return $this;
        }
        
        public function limit($limit, $offset = null){
            $this->_limit = $limit;
            $this->_limit_offset = $offset;
            
            return $this;
        }
        
        public function update(){
            $this->_is_write = true;
            foreach(func_get_args() as $arg){
                if (is_array($arg)){
                    if (is_assoc($arg)){
                        foreach($arg as $alias => $value){
                            if (is_integer($alias)) $alias = $value;
                            $this->_update[$alias] = $value;
                        }
                    }else{
                        foreach($arg as $a){
                            $this->_update[$a] = $a;
                        }
                    }
                }else{
                    if (is_string($arg)){
                        $this->_update[$arg] = $arg;
                    }else{
                        $this->_update[] = $arg;
                    }
                }
            }
            return $this;
        }
        
        public function set($key, $value){
            $this->_set[$key] = $value;
            
            return $this;
        }
        
        public function delete(){
            $this->_is_write = true;
            
            foreach(func_get_args() as $arg){
                
                if(is_array($arg)){
                    foreach($arg as $a){
                        $this->_delete_from[] = $a;
                    }
                }elseif (is_string($arg)){
                    $this->_delete_from[] = $arg;
                }
            }
            
            return $this;
        }
        
        public function delete_from(){
            foreach(func_get_args() as $arg){
                $this->delete($arg);
            }
            
            return $this;
        }
        
        public function insert_into($table_name, $fields = array()){
            $this->_is_write = true;
            $this->_insert_into = array(
                'table_name' => $table_name,
                'fields' => $fields
            );
            
            return $this;
        }
        
        public function values(){
            $args = func_get_args();
            
            if (count($args) == 1 && is_array($args[0])){
                $this->_values[] = $args[0];
            }else{
                $this->_values[] = func_get_args();
            }
            
            return $this;
        }
        
        
        
        /*
         * Create options
         */
        public function create_database($database_name){
            $this->_is_write = true;
            $this->_create_database = $database_name;
            return $this;
        }
        
        public function create_table($table_name){
            $this->_is_write = true;
            $this->_create_table = $table_name;
            return $this;
        }
        
        public function add($field_name, $data_type, $nullable = true, $default_value = null, $unique = false, $signed = true, $after = null){
            $params = func_get_args();
            
            if (count($params) == 1 && is_array($this->_functions) && count($this->_functions)){
                return $this->logical_add($params[0]);
            }
            
            $field_name = $params[0];
            $data_type = $params[1];
            $nullable = $params[2];
            $default_value = $params[3];
            $unique = $params[4];
            $signed = $params[5];
            $after = $params[6];
            
            $this->_fields[] = array(
                'field_name' => $field_name,
                'data_type' => $data_type,
                'nullable' => $nullable,
                'default_value' => $default_value,
                'unique' => $unique,
                'signed' => $signed,
                '_type' => 'add',
                '_after' => $after
            );
            return $this;
        }
        
        public function index($field_name, $size = null){
            $this->_indexes[] = array(
                'field_name' => $field_name,
                'size' => $size
            );
            return $this;
        }
        
        public function primary_key($field_name, $auto_increment = true){
            $this->_primary_keys[] = array(
                'field_name' => $field_name,
                'auto_increment' => $auto_increment
            );
            
            return $this;
        }
        
        public function foreign_key($field_name, $reference_table_name, $reference_field_name, $on_delete = self::ON_DELETE_SET_NULL){
            $this->_foreign_keys[] = array(
                'field_name' => $field_name,
                'reference_table_name' => $reference_table_name,
                'reference_field_name' => $reference_field_name,
                'on_delete' => $on_delete
            );
            
            return $this;
        }
        
        /*
         * Drop options
         */
        public function drop_database($database_name){
            $this->_is_write = true;
            $this->_drop_database = $database_name;
            return $this;
        }
        
        public function drop_table($table_name){
            $this->_is_write = true;
            $this->_drop_table = $table_name;
            return $this;
        }
        
        /*
         * Alter table
         */
        public function alter_table($table_name){
            $this->_is_write = true;
            $this->_alter_table = $table_name;
            return $this;
        }
        
        public function change($old_field_name, $field_name, $data_type, $nullable = true, $default_value = null, $unique = false, $signed = true, $after = null){
            $this->_fields[] = array(
                'old_field_name' => $old_field_name,
                'field_name' => $field_name,
                'data_type' => $data_type,
                'nullable' => $nullable,
                'default_value' => $default_value,
                'unique' => $unique,
                'signed' => $signed,
                '_type' => 'change',
                '_after' => $after
            );
            return $this;
        }
        
        public function drop($field_name){
            $this->_fields[] = array(
                'field_name' => $field_name,
                '_type' => 'drop'
            );
            return $this;
        }
        
        /*
         * Rendering functions
         */
        public function render(){
            if ($this->data_source instanceof \adapt\data_source_sql){
                $sql = $this->data_source->render_sql($this);
                return $sql;
            }
            
            return null;
        }
        
        public function execute($time_to_cache = null){
            $sql = $this->render();
            
            if (!is_null($sql) && $sql != ""){
                if ($this->data_source instanceof \adapt\data_source_sql){
                    $this->data_source->errors(true);
                    if ($this->_is_write){
                        
                        //if (isset($this->_create_table)/* && count($this->data_source->errors()) == 0*/){
                        //    /* We can only create tables if we know the bundle */
                        //    $bundle = $this->store('adapt.installing_bundle');
                        //    
                        //    if (!is_null($bundle) && $bundle != ''){
                        //        
                        //        /* We know the bundle, so lets write */
                        //        $this->data_source->write($sql);
                        //        
                        //        /* Check for errors */
                        //        $errors = $this->data_source->errors(true);
                        //        if (is_array($errors) && count($errors) > 0){
                        //            /* The data source errored, we will take ownership */
                        //            foreach($errors as $error) $this->error($error);
                        //        }else{
                        //            if (!in_array($this->_create_table, array('bundle_version', 'field', 'data_type'))){
                        //                
                        //                /* Creation succeed, yay! */
                        //                
                        //                /* Lets update the schema firstly */
                        //                
                        //                $schema = $this->data_source->schema;
                        //                
                        //                $new_fields = array();
                        //                //print new html_pre(print_r($this->_foreign_keys, true));
                        //                foreach($this->_fields as $field){
                        //                    
                        //                    /* Get any references */
                        //                    $foreign_table = null;
                        //                    $foreign_field = null;
                        //                    
                        //                    foreach($this->_foreign_keys as $key){
                        //                        if ($key['field_name'] == $field['field_name']){
                        //                            $foreign_table = $key['reference_table_name'];
                        //                            $foreign_field = $key['reference_field_name'];
                        //                        }
                        //                    }
                        //                    
                        //                    /* Parse the data type */
                        //                    $data_type = null;
                        //                    $data_type_params = null;
                        //                    $matches = array();
                        //                    if (preg_match_all("/^([_A-Za-z0-9]+)(\((\d+)\))?$/", $field['data_type'], $matches)){
                        //                        $data_type = $this->data_source->get_data_type($matches[1][0]);
                        //                        if (isset($matches[3][0])){
                        //                            $data_type_params = $matches[3][0];
                        //                        }
                        //                    }
                        //                    
                        //                    //print new html_pre("IN: " . print_r($field, true) . "\nOUT: " . print_r($data_type, true));
                        //                    
                        //                    /* Get primary keys and auto_increment */
                        //                    $primary_key = 'No';
                        //                    $auto_increment = 'No';
                        //                    foreach($this->_primary_keys as $key){
                        //                        if ($key['field_name'] == $field['field_name']){
                        //                            $primary_key = 'Yes';
                        //                            if ($key['auto_increment'] == true) $auto_increment = 'Yes';
                        //                        }
                        //                    }
                        //                    
                        //                    
                        //                    $new_fields[] = array(
                        //                        'bundle_name' => $bundle,
                        //                        'table_name' => $this->_create_table,
                        //                        'field_name' => $field['field_name'],
                        //                        'referenced_table_name' => $foreign_table,
                        //                        'referenced_field_name' => $foreign_field,
                        //                        'data_type_id' => $data_type['data_type_id'],
                        //                        'primary_key' => $primary_key,
                        //                        'signed' => $field['signed'] ? 'Yes' : 'No',
                        //                        'nullable' => $field['nullable'] ? 'Yes' : 'No',
                        //                        'auto_increment' => $auto_increment,
                        //                        'timestamp' => $data_type['name'] == 'timestamp' ? 'Yes' : 'No',
                        //                        'max_length' => !is_null($data_type_params) && !in_array(strtolower($data_type['name']), array('enum', 'set')) ? $data_type_params : null,
                        //                        'default_value' => $field['default_value'],
                        //                        'allowed_values' => in_array(strtolower($data_type['name']), array('enum', 'set')) ? "[{$data_type_params}]" : null,
                        //                        'lookup_table' => null,
                        //                        'depends_on_table_name' => null,
                        //                        'depends_on_field_name' => null,
                        //                        'depends_on_value' => null
                        //                    );
                        //                    
                        //                }
                        //                
                        //                //print new html_pre(print_r($new_fields, true));
                        //                
                        //                /* Merge the schema */
                        //                $this->data_source->schema = array_merge($schema, $new_fields);
                        //                //print new html_pre(print_r($this->data_source->schema, true));
                        //                //exit(1);
                        //                
                        //                /* Now insert the new fields into 'field' */
                        //                /* Add the date created field to the new fields */
                        //                foreach($new_fields as &$field){
                        //                    $field['date_created'] = new sql('now()');
                        //                }
                        //                //print new html_pre(print_r($new_fields, true));
                        //                /* Insert the fields */
                        //                $sql = $this->data_source->sql;
                        //                $sql->insert_into('field', array_keys($new_fields[0]));
                        //                //foreach($new_fields as $field){
                        //                for($i = 0; $i < count($new_fields); $i++){
                        //                    //print new html_pre(print_r($field, true));
                        //                    $sql->values(array_values($new_fields[$i]));
                        //                }
                        //                $sql->execute();
                        //            }
                        //        }
                        //        
                        //        
                        //    }else{
                        //        $this->error('Unable to create table, unknown bundle');
                        //    }
                        //    
                        //}elseif(isset($this->_alter_table)){
                        //    
                        //    /******/
                        //    
                        //    
                        //    /* We can only alter tables if we know the bundle */
                        //    $bundle = $this->store('adapt.installing_bundle');
                        //    
                        //    if (!is_null($bundle) && $bundle != ''){
                        //        
                        //        /* We know the bundle, so lets write */
                        //        $this->data_source->write($sql);
                        //        
                        //        /* Check for errors */
                        //        $errors = $this->data_source->errors(true);
                        //        if (is_array($errors) && count($errors) > 0){
                        //            /* The data source errored, we will take ownership */
                        //            foreach($errors as $error) $this->error($error);
                        //        }else{
                        //            //if (!in_array($this->_alter_table, array('field', 'data_type'))){
                        //                
                        //                /* Lets update the schema with the new values */
                        //                $schema = $this->data_source->schema;
                        //                
                        //                foreach($this->_fields as $field){
                        //                    
                        //                    /* Parse the data type */
                        //                    $data_type = null;
                        //                    $data_type_params = null;
                        //                    $matches = array();
                        //                    if (preg_match_all("/^([_A-Za-z0-9]+)(\((.+)\))?$/", $field['data_type'], $matches)){
                        //                        $data_type = $this->data_source->get_data_type($matches[1]);
                        //                        if (isset($matches[3])){
                        //                            $data_type_params = $matches[3];
                        //                        }
                        //                    }
                        //                    
                        //                    switch($field['_type']){
                        //                    case "add":
                        //                        /* Lets add the field to the schema */
                        //                        $new_field = array(
                        //                            'bundle_name' => $bundle,
                        //                            'table_name' => $this->_alter_table,
                        //                            'field_name' => $field['field_name'],
                        //                            //TODO: Foreign keys
                        //                            'data_type_id' => $data_type,
                        //                            //TODO: Primary keys
                        //                            'signed' => $field['signed'] ? 'Yes' : 'No',
                        //                            'nullable' => $field['nullable'] ? 'Yes' : 'No',
                        //                            //TODO: auto_increment
                        //                            'timestamp' => $data_type['name'] == 'timestamp' ? 'Yes' : 'No',
                        //                            'max_length' => !is_null($data_type_params) && !in_array(strtolower($data_type['name']), array('enum', 'set')) ? $data_type_params : null,
                        //                            'default_value' => $field['default_value'],
                        //                            'allowed_values' => in_array(strtolower($data_type['name']), array('enum', 'set')) ? "[{$data_type_params}]" : null,
                        //                            'lookup_table' => null,
                        //                            'depends_on_table_name' => null,
                        //                            'depends_on_field_name' => null,
                        //                            'depends_on_value' => null
                        //                        );
                        //                        
                        //                        $schema[] = $new_field;
                        //                        break;
                        //                    case "change":
                        //                        /* Lets update a field in the schema */
                        //                        
                        //                        foreach($schema as &$schema_field){
                        //                            if ($schema_field['table_name'] == $this->_alter_table && $schema_field['field_name'] == $field['old_field_name']){
                        //                                $schema_field['bundle_name'] = $bundle;
                        //                                $schema_field['field_name'] = $field['field_name'];
                        //                                //TODO: Foreign keys
                        //                                $schema_field['data_type_id'] = $data_type;
                        //                                //TODO: Primary keys
                        //                                $schema_field['signed'] = $field['signed'] ? 'Yes' : 'No';
                        //                                $schema_field['nullable'] = $field['nullable'] ? 'Yes' : 'No';
                        //                                $schema_field['timestamp'] = $data_type['name'] == 'timestamp' ? 'Yes' : 'No';
                        //                                $schema_field['max_length'] = !is_null($data_type_params) && !in_array(strtolower($data_type['name']), array('enum', 'set')) ? $data_type_params : null;
                        //                                $schema_field['default_value'] = $field['default_value'];
                        //                            }
                        //                        }
                        //                        
                        //                        break;
                        //                    case "drop":
                        //                        /* Lets remove the field from the schema */
                        //                    }
                        //                }
                        //                
                        //                
                        //                /* Creation succeed, yay! */
                        //                
                        //                /* Lets update the schema firstly */
                        //                
                        //                $schema = $this->data_source->schema;
                        //                
                        //                $new_fields = array();
                        //                //print new html_pre(print_r($this->_foreign_keys, true));
                        //                foreach($this->_fields as $field){
                        //                    
                        //                    /* Get any references */
                        //                    $foreign_table = null;
                        //                    $foreign_field = null;
                        //                    
                        //                    foreach($this->_foreign_keys as $key){
                        //                        if ($key['field_name'] == $field['field_name']){
                        //                            $foreign_table = $key['reference_table_name'];
                        //                            $foreign_field = $key['reference_field_name'];
                        //                        }
                        //                    }
                        //                    
                        //                    /* Parse the data type */
                        //                    $data_type = null;
                        //                    $data_type_params = null;
                        //                    $matches = array();
                        //                    if (preg_match_all("/^([_A-Za-z0-9]+)(\((.+)\))?$/", $field['data_type'], $matches)){
                        //                        $data_type = $this->data_source->get_data_type($matches[1]);
                        //                        if (isset($matches[3])){
                        //                            $data_type_params = $matches[3];
                        //                        }
                        //                    }
                        //                    
                        //                    /* Get primary keys and auto_increment */
                        //                    $primary_key = 'No';
                        //                    $auto_increment = 'No';
                        //                    foreach($this->_primary_keys as $key){
                        //                        if ($key['field_name'] == $field['field_name']){
                        //                            $primary_key = 'Yes';
                        //                            if ($key['auto_increment'] == true) $auto_increment = 'Yes';
                        //                        }
                        //                    }
                        //                    
                        //                    
                        //                    $new_fields[] = array(
                        //                        'bundle_name' => $bundle,
                        //                        'table_name' => $this->_create_table,
                        //                        'field_name' => $field['field_name'],
                        //                        'referenced_table_name' => $foreign_table,
                        //                        'referenced_field_name' => $foreign_field,
                        //                        'data_type_id' => $data_type['data_type_id'],
                        //                        'primary_key' => $primary_key,
                        //                        'signed' => $field['signed'] ? 'Yes' : 'No',
                        //                        'nullable' => $field['nullable'] ? 'Yes' : 'No',
                        //                        'auto_increment' => $auto_increment,
                        //                        'timestamp' => $data_type['name'] == 'timestamp' ? 'Yes' : 'No',
                        //                        'max_length' => !is_null($data_type_params) && !in_array(strtolower($data_type['name']), array('enum', 'set')) ? $data_type_params : null,
                        //                        'default_value' => $field['default_value'],
                        //                        'allowed_values' => in_array(strtolower($data_type['name']), array('enum', 'set')) ? "[{$data_type_params}]" : null,
                        //                        'lookup_table' => null,
                        //                        'depends_on_table_name' => null,
                        //                        'depends_on_field_name' => null,
                        //                        'depends_on_value' => null
                        //                    );
                        //                    
                        //                }
                        //                
                        //                //print new html_pre(print_r($new_fields, true));
                        //                
                        //                /* Merge the schema */
                        //                $this->data_source->schema = array_merge($schema, $new_fields);
                        //                //print new html_pre(print_r($this->data_source->schema, true));
                        //                //exit(1);
                        //                
                        //                /* Now insert the new fields into 'field' */
                        //                /* Add the date created field to the new fields */
                        //                foreach($new_fields as &$field){
                        //                    $field['date_created'] = new sql('now()');
                        //                }
                        //                //print new html_pre(print_r($new_fields, true));
                        //                /* Insert the fields */
                        //                $sql = $this->data_source->sql;
                        //                $sql->insert_into('field', array_keys($new_fields[0]));
                        //                //foreach($new_fields as $field){
                        //                for($i = 0; $i < count($new_fields); $i++){
                        //                    //print new html_pre(print_r($field, true));
                        //                    $sql->values(array_values($new_fields[$i]));
                        //                }
                        //                //print new html_pre($sql);
                        //                $sql->execute();
                        //            //}
                        //        }
                        //        
                        //        
                        //    }else{
                        //        $this->error('Unable to create table, unknown bundle');
                        //    }
                        //    
                        //    
                        //    /*******/
                        //    
                        //    
                        //}else{
                            /* Write to the data source */
                            $this->data_source->write($sql);
                            
                            /* If the data source has errored then we take responibility for it */
                            $errors = $this->data_source->errors(true);
                            if (is_array($errors) && count($errors) > 0){
                                foreach($errors as $error) $this->error($error);
                            }
                        //}
                        
                        
                        //$this->data_source->write($sql);
                        //
                        //if (isset($this->_create_table)/* && count($this->data_source->errors()) == 0*/){
                        //    print "fubar";
                        //    $bundle = $this->store('adapt.installing_bundle');
                        //    //if ()
                        //    print "<h4>NS:{$namespace}</h4>";
                        //    
                        //    if (!in_array($this->_create_table, array('setting', 'data_type', 'field'))){
                        //        
                        //    }
                        //    
                        //    /*
                        //     * The write was successful so we are going to record
                        //     * the table in adapt_field and adapt_reference
                        //     */
                        //    
                        //    /*
                        //     * We need to get the bundle_id of the bundle
                        //     * creating this table, so we need to read the
                        //     * manifest.
                        //     * Before we can do that we need to workout
                        //     * the calling namespace.
                        //     */
                        //    
                        //    
                        //    
                        //    /* We need to update the schema */
                        //    $schema = $this->data_source->schema;
                        //    $schema['tables'][$this->_create_table] = array();
                        //    
                        //    /*
                        //     * If we are creating the adapt data tables then there is
                        //     * a chance that loading the schema will cause a datasource error,
                        //     * so we need to reset the data_source error log before we continure
                        //     */
                        //    $this->data_source->errors(true);
                        //    
                        //    $sql = new sql();
                        //    $sql->insert_into('adapt_field', array('table_name', 'field_name', 'primary_key', 'data_type', 'signed', 'nullable', 'default_value', 'auto_increment', 'timestamp', 'date_created', 'date_modified'));
                        //    
                        //    $keys = $this->_primary_keys;
                        //    
                        //    foreach($this->_fields as $row){
                        //        $key = 'No';
                        //        $auto_increment = 'No';
                        //        foreach($keys as $k){
                        //            if ($k['field_name'] == $row['field_name']){
                        //                $key = 'Yes';
                        //                
                        //                if ($k['auto_increment']) $auto_increment = 'Yes';
                        //            }
                        //        }
                        //        $sql->values($this->_create_table, $row['field_name'], $key, $row['data_type'], $row['signed'] ? 'Yes' : 'No', $row['nullable'] ? 'Yes' : 'No', $row['default_value'], $auto_increment, $row['data_type'] == 'timestamp' ? 'Yes' : 'No', new sql('now()'), new sql('now()'));
                        //        
                        //        $schema['tables'][$this->_create_table][$row['field_name']] = array(
                        //            'primary_key' => $key,
                        //            'data_type' => $row['data_type'],
                        //            'signed' => $row['signed'] ? 'Yes' : 'No',
                        //            'nullable' => $row['nullable'] ? 'Yes' : 'No',
                        //            'default_value' => $row['default_value'],
                        //            'auto_increment' => $auto_increment,
                        //            'timestamp' => $row['data_type'] == 'timestamp' ? 'Yes' : 'No',
                        //            'min_size' => null,
                        //            'max_size' => null,
                        //            'date_created' => null,
                        //            'date_modified' => null,
                        //            'date_deleted' => null
                        //        );
                        //        
                        //    }
                        //    $this->data_source->schema = $schema;
                        //    
                        //    //print $sql;
                        //    $sql->execute();
                        //    
                        //    /* Add any references */
                        //    if (count($this->_foreign_keys)){
                        //        $sql = new sql();
                        //        $sql->insert_into('adapt_reference', array('table_name', 'field_name', 'referenced_table_name', 'referenced_field_name', 'date_created', 'date_modified'));
                        //        
                        //        foreach($this->_foreign_keys as $key){
                        //            $sql->values($this->_create_table, $key['field_name'], $key['referenced_table_name'], $key['referenced_field_name'], new sql('now()'), new sql('now()'));
                        //        }
                        //        
                        //        $sql->execute();
                        //    }
                        //    
                        //}
                    }else{
                        /* Are the results cached? */
                        $results = null;
                        if ($time_to_cache !== 0){
                            $results = $this->cache->get_sql($sql);
                        }
                        
                        if (is_array($results)){
                            $this->_results = $results;
                        }else{
                            //print new html_pre("Unable to pull from cache: {$sql}");
                            if ($sth = $this->data_source->read($sql)){
                                $results = $this->data_source->fetch($sth, \adapt\data_source_sql::FETCH_ALL_ASSOC);
                                if ($time_to_cache !== 0){
                                    $this->cache->sql($sql, $results, $time_to_cache);
                                }
                                $this->_results = $results;
                            }
                        }
                        
                    }
                }
            }
            
            /* Reset the object */
            $this->_is_write = false;
            $this->_has_written = false;
            
            $this->_sql_statement = null;
            $this->_select_fields = array();
            $this->_is_distinct = false;
            $this->_from = array();
            $this->_join_conditions = array();
            $this->_where_conditions = array();
            $this->_having_conditions = array();
            $this->_grouping = array();
            $this->_ordering = array();
            $this->_limit = null;
            $this->_limit_offset = null;
            $this->_create_database = null;
            $this->_create_table = null;
            $this->_fields = array();
            $this->_indexes = array();
            $this->_primary_keys = array();
            $this->_foreign_keys = array();
            $this->_insert_into = array();
            $this->_values = array();
            $this->_update = array();
            $this->_set = array();
            $this->_alter_table = null;
            $this->_delete_from = array();
            $this->_drop_database = null;
            $this->_drop_table = null;
            
            return $this;
        }
        
        
        public function results(){
            $results = $this->_results;
            $this->_results = array();
            return $results;
        }
        
        public function id(){
            if ($this->data_source instanceof \adapt\data_source_sql){
                return $this->data_source->last_insert_id();
            }
            
            return null;
        }
        
        ///*
        // * Adapt SQL Parsing functions
        // */
        //protected function parse_strings(&$string, &$output){
        //    $matches = array();
        //    $quotes = array();
        //    
        //    if (preg_match_all("/\\\\\"|\\\'/", $string, $matches)){
        //        for($i = 0; $i < count($matches[0]); $i++){
        //            $string = str_replace($matches[0][$i], "%q{$i}", $string);
        //            $quotes[] = $matches[0][$i];
        //        }
        //    }
        //    
        //    if (preg_match_all("/('.*?'|\".*?\")/", $string, $matches)){
        //        for($i = 0; $i < count($matches[0]); $i++){
        //            $string = str_replace($matches[0][$i], "%string{$i}", $string);
        //            $quotes_text = $matches[0][$i];
        //            for($j = 0; $j < count($quotes); $j++){
        //                $quotes_text = str_replace("%q{$j}", $quotes[$j], $quotes_text);
        //            }
        //            
        //            $output["string{$i}"] = $quotes_text;
        //        }
        //    }
        //}
        //
        //protected function parse_numbers(&$string, &$output){
        //    $matches = array();
        //    if (preg_match_all("/\b([0-9]+)\b/", $string, $matches)){
        //        for($i = 0; $i < count($matches[0]); $i++){
        //            $string = preg_replace("/\b([0-9]+)\b/", "%number{$i}", $string, 1);
        //            $output["number{$i}"] = $matches[0][$i];
        //        }
        //    }
        //}
        //
        //protected function parse_keywords(&$string, &$output){
        //    $keywords = array(
        //        'select distinct',
        //        'select',
        //        'delete from',
        //        'delete',
        //        'from',
        //        'where',
        //        'having',
        //        'order by',
        //        'limit',
        //        'left join',
        //        'right join',
        //        'join',
        //        'update',
        //        'insert into',
        //        'values',
        //        'set',
        //        'create table',
        //        'as',
        //        'using',
        //        'on',
        //        'union',
        //        'and',
        //        'or'
        //    );
        //    $matches = array();
        //    
        //    for($i = 0; $i < count($keywords); $i++){
        //        $keyword = $keywords[$i];
        //        if (preg_match_all("/\b{$keyword}\b/", $string, $matches)){
        //            for($j = 0; $j < count($matches[0]); $j++){
        //                $string = preg_replace("/\b{$matches[0][$j]}\b/", "%keyword{$i}", $string);
        //                //$string = str_replace($matches[0][$j], "%keyword{$i}", $string);
        //                $output["keyword{$i}"] = $matches[0][$j];
        //            }
        //        }
        //    }
        //}
        //
        //protected function parse_functions(&$string, &$output){
        //    $functions = array(
        //        /* String functions */
        //        'ascii',
        //        'bin',
        //        'bit_length',
        //        'char',
        //        'char_length',
        //        'character_length',
        //        'concat',
        //        'concat_ws',
        //        'elt',
        //        'export_set',
        //        'field',
        //        'find_in_set',
        //        'format',
        //        'hex',
        //        'insert',
        //        'instr',
        //        'lcase',
        //        'left',
        //        'length',
        //        'load_file',
        //        'locate',
        //        'lower',
        //        'lpad',
        //        'ltrim',
        //        'make_set',
        //        'match',
        //        'mid',
        //        'oct',
        //        'octet_length',
        //        'ord',
        //        'position',
        //        'quote',
        //        'repeat',
        //        'replace',
        //        'reverse',
        //        'right',
        //        'rpad',
        //        'rtrim',
        //        'soundex',
        //        'space',
        //        'strcmp',
        //        'substr',
        //        'substring_index',
        //        'substring',
        //        'trim',
        //        'ucase',
        //        'unhex',
        //        'upper',
        //        /* Control flow */
        //        'if',
        //        'ifnull',
        //        'nullif',
        //        /* Numeric functions */
        //        'abs',
        //        'acos',
        //        'asin',
        //        'atan',
        //        'atan2',
        //        'ceil',
        //        'ceiling',
        //        'conv',
        //        'cos',
        //        'cot',
        //        'crc32',
        //        'degrees',
        //        'exp',
        //        'floor',
        //        'ln',
        //        'log10',
        //        'log2',
        //        'log',
        //        'mod',
        //        'pi',
        //        'pow',
        //        'power',
        //        'radians',
        //        'rand',
        //        'round',
        //        'sign',
        //        'sin',
        //        'sqrt',
        //        'tan',
        //        'truncate',
        //        /* Date/time functions */
        //        'adddate',
        //        'addtime',
        //        'convert_tz',
        //        'curdate',
        //        'current_date',
        //        'current_time',
        //        'current_timestamp',
        //        'curtime',
        //        'date_add',
        //        'date_format',
        //        'date_sub',
        //        'date',
        //        'datediff',
        //        'day',
        //        'dayname',
        //        'dayofmonth',
        //        'dayofweek',
        //        'dayofyear',
        //        'extract',
        //        'from_days',
        //        'from_unixtime',
        //        'get_format',
        //        'hour',
        //        'last_day',
        //        'localtime',
        //        'localtimestamp',
        //        'makedate',
        //        'maketime',
        //        'microsecond',
        //        'minute',
        //        'month',
        //        'monthname',
        //        'now',
        //        'period_add',
        //        'period_diff',
        //        'quarter',
        //        'sec_to_time',
        //        'second',
        //        'str_to_date',
        //        'subdate',
        //        'subtime',
        //        'sysdate',
        //        'time_format',
        //        'time_to_sec',
        //        'time',
        //        'timediff',
        //        'timestamp',
        //        'timestampadd',
        //        'timestampdiff',
        //        'to_days',
        //        'to_seconds',
        //        'unix_timestamp',
        //        'utc_date',
        //        'utc_time',
        //        'utc_timestamp',
        //        'week',
        //        'weekday',
        //        'weekofyear',
        //        'year',
        //        'yearweek',
        //        /* Encryption functions */
        //        'aes_decrypt',
        //        'aes_encrypt',
        //        'compress',
        //        'decode',
        //        'encode',
        //        'md5',
        //        'password',
        //        'sha',
        //        'sha2',
        //        'sha2',
        //        'uncompress',
        //        'uncompressed_length'
        //    );
        //    $matches = array();
        //    
        //    for($i = 0; $i < count($functions); $i++){
        //        $function = $functions[$i];
        //        if (preg_match_all("/\b{$function}\(/", $string, $matches)){
        //            for($j = 0; $j < count($matches[0]); $j++){
        //                //$string = preg_replace("/\b{$matches[0][$j]}\b/", "%function{$i}", $string);
        //                $string = str_replace($matches[0][$j], "%function{$i}(", $string);
        //                $output["function{$i}"] = $matches[0][$j];
        //            }
        //        }
        //    }
        //}
        //
        //
        //protected function parse_operators(&$string, &$output){
        //    $operators = array(
        //        //'and',
        //        '&&',
        //        "=",
        //        ':=',
        //        'between',
        //        'binary',
        //        '&',
        //        '~',
        //        '\|\|',
        //        '\|',
        //        '^',
        //        'case',
        //        'div',
        //        '\/',
        //        '<=>',
        //        '>=',
        //        '>',
        //        'is not null',
        //        'is null',
        //        'is not',
        //        'is',
        //        '<<',
        //        '<=',
        //        '<',
        //        'like',
        //        '-',
        //        //'%',
        //        'mod',
        //        '!=',
        //        '<>',
        //        'not like',
        //        'not regexp',
        //        'not',
        //        '!',
        //        //'or',
        //        '\+',
        //        'regexp',
        //        '>>',
        //        'rlike',
        //        'sounds like',
        //        '\*',
        //        'xor'
        //    );
        //    $matches = array();
        //    
        //    for($i = 0; $i < count($operators); $i++){
        //        $operator = $operators[$i];
        //        if (preg_match_all("/({$operator})/", $string, $matches)){
        //            for($j = 0; $j < count($matches[1]); $j++){
        //                $string = str_replace($matches[1][$j], " %operator{$i} ", $string);
        //                $output["operator{$i}"] = $matches[1][$j];
        //            }
        //        }
        //    }
        //}
        //
        //
        //protected function extract_tables(&$string, &$sections){
        //    
        //    $matches = array();
        //    $tables = array();
        //    $aliases = array();
        //    
        //    if (preg_match_all("/%keyword(2|4|9|10|11|12|13|16)\s([_A-Za-z0-9]+)(\s%keyword17\s([_A-Za-z0-9]+|%string([0-9]+)))?/", $string, $matches)){
        //        //print_r($matches);
        //        //exit(1);
        //        if (count($matches[2])){
        //            for($i = 0; $i < count($matches[2]); $i++){
        //                if (!in_array($matches[2][$i], $tables)){
        //                    $tables[] = $matches[2][$i];
        //                    $count = count($aliases);
        //                    $aliases[$matches[2][$i]] = array($matches[2][$i]);
        //                    $string = preg_replace("/%keyword(2|4|9|10|11|12|13|16)\s{$matches[2][$i]}/m", "%keyword{$matches[1][$i]} %table{$i}", $string);
        //                    $sections["table{$i}"] = $matches[2][$i];
        //                    
        //                    $sections["table_alias{$count}"] = $matches[2][$i];
        //                }
        //                
        //                if (isset($matches[4][$i]) && $matches[4][$i] != ""){
        //                    $count = count($aliases);
        //                    $aliases[$matches[4][$i]][] = $matches[2][$i];
        //                    $string = preg_replace("/%keyword(2|4|9|10|11|12|13|16)\s%table{$i}(\s%keyword17\s({$matches[4][$i]}))?/m", "%keyword{$matches[1][$i]} %table{$i} %keyword17 %table_alias{$count}", $string);
        //                    $sections["table_alias{$count}"] = $matches[4][$i];
        //                }
        //                
        //                
        //                
        //            }
        //        }
        //        
        //        $sections['tables'] = $tables;
        //        $sections['table_aliases'] = $aliases;
        //    }
        //}
        //
        //public function parse_parentheses(&$string, &$sections, &$errors){
        //    /* Make sure the count matches */
        //    $open_count = 0;
        //    $closed_count = 0;
        //    
        //    $matches = array();
        //    
        //    if (preg_match_all("/\(/", $string, $matches)){
        //        $open_count = count($matches[0]);
        //    }
        //    
        //    if (preg_match_all("/\)/", $string, $matches)){
        //        $closed_count = count($matches[0]);
        //    }
        //    
        //    if ($open_count > 0){
        //        if ($open_count == $closed_count){
        //            
        //            
        //            
        //            for($i = 0; $i < $open_count; $i++){
        //                $string = strrev($string);
        //                list($post, $pre) = explode("(", $string, 2);
        //                
        //                $post = strrev($post);
        //                $pre = strrev($pre);
        //                
        //                list ($data, $post) = explode(")", $post, 2);
        //                $sections["section{$i}"] = $data;
        //                
        //                $string = $pre . "%section{$i}" . $post;
        //            }
        //            
        //        }else{
        //            $errors[] = "Brackets don't match :(";
        //        }
        //    }
        //}
        //
        //public function parse_symbols($string, $dictonary){
        //    //print_r($dictonary);
        //    print "Symbols: {$string}\n";
        //    
        //    $select_fields = array();
        //    
        //    $string = trim($string);
        //    
        //    $parts = explode(" ", $string);
        //    
        //    print_r($parts);
        //    $type = "";
        //    
        //    
        //    foreach($parts as $part){
        //        switch($part){
        //        case "%keyword1": //Select
        //            $type = "select";
        //            break;
        //        case "%keyword4": //From
        //            $type = "from";
        //            break;
        //        default:
        //            if ($type == "select"){
        //                $this->select($part);
        //            }
        //        }
        //        
        //        
        //    }
        //    
        //    print_r($select_fields);
        //    
        //}
        //
        //public function parse($sql){
        //    $dictonary = array();
        //    $this->parse_operators($sql, $dictonary);
        //    $this->parse_strings($sql, $dictonary);
        //    $this->parse_numbers($sql, $dictonary);
        //    $this->parse_keywords($sql, $dictonary);
        //    $this->parse_functions($sql, $dictonary);
        //    //$this->parse_operators($sql, $dictonary);
        //    $sql = str_replace("\n", " ", $sql);
        //    $sql = preg_replace("/\s+/", " ", $sql);
        //    $this->extract_tables($sql, $dictonary);
        //    $errors = array();
        //    $this->parse_parentheses($sql, $dictonary, $errors);
        //    $this->parse_symbols($sql, $dictonary);
        //    print_r($dictonary);
        //    print $sql;
        //    
        //    /****/
        //    $parts = explode("%", $sql);
        //    print_r($parts);
        //}
        
    }
    
}

?>