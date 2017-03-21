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
 *
 */
namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * The foundation class for models
     *
     * @property-read $data_source
     * The current data source used by this model
     * @property-read array $schema
     * The schema of this model
     * @property-read boolean $is_loaded
     * Has this model been loaded?
     * @property-read boolean $has_changed
     * Has this model changed?
     * @property-read boolean $is_valid
     * Is this model valid?
     * @property-read string $table_name
     * The table name this model is using.
     * @property-read boolean $auto_load_children
     * Are child models auto loaded?
     */
    class model extends base{
        
        /**
         * Fired when a model is loaded
         */
        const EVENT_ON_LOAD = 'model.on_load';
        
        /**
         * Fired when a model is loaded by name
         */
        const EVENT_ON_LOAD_BY_NAME = 'model.on_load_by_name';
        
        /**
         * Fired when a model is loaded by data
         */
        const EVENT_ON_LOAD_BY_DATA = 'model.on_load_by_data';
        
        /**
         * Fired when a model is loaded by GUID
         */
        const EVENT_ON_LOAD_BY_GUID = 'model.on_load_by_guid';
        
        /**
         * Fired when a model is saved
         */
        const EVENT_ON_SAVE = 'model.on_save';
        
        /**
         * Fired when a model is deleted
         */
        const EVENT_ON_DELETE = 'model.on_delete';
        
        /**
         * Fired when a child model is added to this model
         */
        const EVENT_ON_ADD = 'model.on_add';
        
        /**
         * Fired when a data is pushed into this model
         */
        const EVENT_ON_PUSH = 'model.on_push';
        
        /** @ignore */
        protected $_data_source;
        
        /** @ignore */
        protected $_table_name;
        
        /** @ignore */
        protected $_schema;
        
        /** @ignore */
        protected $_is_loaded;
        
        /** @ignore */
        protected $_has_changed;
        
        /** @ignore */
        protected $_children;
        
        /** @ignore */
        protected $_children_loaded;
        
        /** @ignore */
        protected $_auto_load_children = false;
        
        /** @ignore */
        protected $_auto_load_only_tables;
        
        /** @ignore */
        protected $_data;
        
        /** @ignore */
        protected $_changed_fields;
        
        /**
         * Constructor
         *
         * @access public
         * @param string
         * The table name for this model
         * @param string|integer
         * The ID of the record to load
         * @param data_source
         * The data source containing the table used by this model. When null
         * the default data source is used.
         */
        public function __construct($table_name, $id = null, $data_source = null){
            parent::__construct();
            $this->_data_source = $data_source;
            $this->_table_name = $table_name;
            
            if ($schema = $this->data_source->get_row_structure($table_name)){
                $this->_schema = $schema;
            }else{
                $this->_table_name = null;
            }
            
            $this->initialise();
            
            if (isset($id)){
                if (is_numeric($id)){
                    $this->load($id);
                }elseif(is_string($id)){
                    $this->load_by_name($id);
                }
            }
        }
        
        /**
         * Initialises this model
         */
        public function initialise(){
            $this->_data = array();
            $this->_changed_fields = array();
            $this->_children = array();
            $this->_children_loaded = false;
            $this->_auto_load_children = false;
            $this->_auto_load_only_tables = array();
            $this->_is_loaded = false;
            $this->_has_changed = false;
            
            if (isset($this->_table_name) && is_array($this->schema)){
                
                foreach($this->schema as $field){
                    if ($field['timestamp'] == "Yes" || $field['field_name'] == 'date_created'){
                        $this->_data[$field['field_name']] = new sql_now();
                    }else{
                        $this->_data[$field['field_name']] = $field['default_value'];
                    }
                    
                }
            }
        }
        
        /*
         * Properties
         */
        /** @ignore */
        public function pget_data_source(){
            if (isset($this->_data_source) && $this->_data_source instanceof data_source){
                return $this->_data_source;
            }
            
            return parent::pget_data_source();
        }
        
        /** @ignore */
        public function pget_schema(){
            return $this->_schema;
        }
        
        /** @ignore */
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        /** @ignore */
        public function pget_has_changed(){
            return $this->_has_changed;
        }
        
        /** @ignore */
        public function pget_is_valid(){
            if (count($this->errors()) == 0){
                
                /*
                 * We need to check all mandatory fields are
                 * satisfied
                 */
                $schema = $this->schema;
                
                foreach($schema as $data){
                    $name = $data['field_name'];
                    if ($data['nullable'] == 'No'){
                        $null = new sql_null();
                        if (is_null($this->_data[$name]) || ($this->_data[$name] instanceof sql && $this->_data[$name]->render() == $null->render())){
                            $label = $name;
                            if (!is_null($data['label'])) $label = $data['label'];
                            $this->error("{$label} cannot be null");
                        }
                    }
                }
            }
            
            //TODO: Dependencies

            /* Valid? */
            return count($this->errors()) == 0 ? true : false;
        }
        
        /** @ignore */
        public function pget_table_name(){
            return $this->_table_name;
        }
        
        /** @ignore */
        public function pget_auto_load_children(){
            return $this->_auto_load_children;
        }
        
        /*
         * Dynamic functions
         */
        /** @ignore */
        public function __get($key){
            $return = parent::__get($key);
            
            if (is_null($return)){
                $fields = array_keys($this->_data);
                    
                if (in_array($key, $fields)){
                    /* Format and return the value */
                    if (is_null($this->_data[$key])) {
                        return null;
                    } else {
                        return $this->data_source->format($this->_table_name, $key, $this->_data[$key]);
                    }
                }
                
            }
            
            return $return;
        }
        
        /** @ignore */
        public function __set($key, $value){
            $return = parent::__set($key, $value);
            
            if ($return === false){
                $fields = array_keys($this->_data);
                
                if (in_array($key, $fields)){
                    $return = true;
                    
                    if ($value instanceof sql){
                        $this->_has_changed = true;
                        $this->_changed_fields[$key] = array(
                            'old_value' => $this->_data[$key],
                            'new_value' => $value
                        );
                        
                        $this->_data[$key] = $value;
                    }else{
                        /* Unformat the value */
                        $value = $this->data_source->unformat($this->table_name, $key, $value);
                        
                        /* Has the value changed? */
                        if ($this->_data[$key] !== $value){
                            
                            /* Is the new value valid? */
                            if ($this->data_source->validate($this->_table_name, $key, $value)){
                                $this->_has_changed = true;
                                $this->_changed_fields[$key] = array(
                                    'old_value' => $this->_data[$key],
                                    'new_value' => $value
                                );
                                
                                $this->_data[$key] = $value;
                                
                            }else{
                                $errors = $this->data_source->errors(true);
                                foreach($errors as $error) $this->error($error);
                            }
                            
                        }
                    }
                    
                    
                }
            }
            
            return $return;
        }
        
        /** @ignore */
        public function _get_data(){
            return $this->_data;
        }
        
        /**
         * Adds a child model to this model.
         *
         * @access public
         * @param model
         * The model to add.
         * @return boolean
         * **true** is successful.
         */
        public function add($data){
            if (is_array($data) && !is_assoc($data)){
                foreach($data as $item) $this->add($item);
            }else{
                if (is_object($data) && $data instanceof base && !$this->has($data)){
                    $this->_children[] = $data;
                    $this->trigger(self::EVENT_ON_ADD, array('added_item' => $data));
                    return true;
                }else{
                    $this->error('Unable to add item, item must be an instance of \\adapt\\base');
                }
            }
            
            return false;
        }
        
        /**
         * Returns the child model at $index or when $index
         * is null an array containing all children.
         *
         * @access public
         * @param integer
         * The index of the child to get.
         * @return model|model[]
         */
        public function get($index = null){
            if (is_null($index)){
                return $this->_children;
            }elseif(is_int($index) && $index >= 0 && $index < count($this->_children)){
                return $this->_children[$index];
            }
        }
        
        /**
         * Changes a child model at $index
         *
         * @access public
         * @param integer
         * The index of the child to replace
         * @param model
         * The new child
         */
        public function set($index, $item){
            if (is_int($index) && $index >= 0 && $index < count($this->_children)){
                $this->_children[$index] = $item;
            }
        }
        
        
        /**
         * Removes a child model, the $index_or_child is null all
         * children are removed.
         *
         * @access public
         * @param integer|model
         * The index of the child to remove or the child itself
         */
        public function remove($index_or_child = null){
            if (is_object($index_or_child)){
                for($i = 0; $i < count($this->_children); $i++){
                    if ($this->_children[$i] === $index_or_child){
                        $this->_children = array_remove($this->_children, $i);
                    }
                }
            }elseif(is_int($index_or_child) && $index_or_child >= 0 && $index_or_child < count($this->_children)){
                $this->_children = array_remove($this->_children, $index_or_child);
            }elseif(is_null($index_or_child)){
                $this->_children = array();
            }
        }
        
        /**
         * Clears all child models
         */
        public function clear(){
            $this->remove();
        }
        
        /**
         * Returns the count of child models
         *
         * @access public
         * @return integer
         */
        public function count(){
            return count($this->_children);
        }
        
        /**
         * Tests if this model has a child model
         *
         * @access public
         * @param model
         * The child to check for
         * @return boolean
         */
        public function has($child){
            foreach($this->_children as $c){
                if ($c === $child) return true;
            }
            
            return false;
        }
        
        /**
         * Returns a list of errors that have occured until now.
         *
         * @access public
         * @param boolean
         * When true the errors are cleared.
         * @return array
         * Returns an array of string messages
         */
        public function errors($clear = false){
            /* Overriden to so it can include children */
            $errors = parent::errors($clear);
            $children = $this->get();
            foreach($children as $child){
                if ($child instanceof base){
                    /* The child may not be an instance of model but
                     * we can still use it if its and instance of \adapt\base
                     * because the error code is compatible.
                     */
                    $errors = array_merge($errors, $child->errors($clear));
                }
            }
            
            return $errors;
        }
        
        /**
         * Returns a clone of the model, the clone is unsaved.
         *
         * @access public
         * @return model
         */
        public function copy(){
            $class = get_class($this);
            $copy = new $class;
            
            /* Clone the data */
            $fields = array_keys($this->_data);
            $keys = $this->data_source->get_primary_keys($this->_table_name);
            
            foreach($fields as $field){
                if (!in_array($field, array_merge($keys, array('date_created', 'date_deleted', 'date_modified')))){
                    $copy->$field = $this->$field;
                }
            }
            
            /* Clone the children */
            $children = $this->get();
            foreach($children as $child){
                if ($child instanceof model){
                    $copy->add($child->copy());
                }
            }
            
            return $copy;
        }
        
        /**
         * Loads the model with the $id
         *
         * @access public
         * @param integer
         * The ID to load the model with
         * @return boolean
         * **true** is successful
         */
        public function load($id){
            $this->initialise();
            
            /* Make sure we have an id */
            if (isset($id)){
                /* Change $id to an array if it isn't already */
                if (!is_array($id)) $id = array($id);
                
                /* Get the fields and keys for this table */
                $fields = array_keys($this->_data);
                $keys = $this->data_source->get_primary_keys($this->_table_name);
                
                /* Make sure the $id count is the same as the $key count */
                if (count($keys) == count($id)){
                    
                    /* If $id is an assoc array then we need to ensure the key names match */
                    if (is_assoc($id)){
                        $id_keys = array_keys($id);
                        $new_ids = array();
                        
                        $match = true;
                        foreach($keys as $key){
                            if (!in_array($key, $id_keys)){
                                $match = false;
                                $this->error('Unable to load, missing key ' . $key);
                            }else{
                                $new_ids[] = $id_keys[$key];
                            }
                        }
                        
                        /* Key mismatch - fail */
                        if (!$match) return false;
                        
                        $id = $new_ids;
                    }
                    
                    /* Lets start to build the sql statement */
                    $sql = $this->data_source->sql; //Taken from the data_source to ensure we query the correct data_source
                    
                    $sql->select("*")->from($this->_table_name);
                    
                    
                    /* If there are multiple keys we need to use a sql_and */
                    $where = new sql_and();
                    
                    if (count($keys) > 1){
                        for($i = 0; $i < count($keys); $i++){
                            $where->add(new sql_cond($keys[$i], sql::EQUALS, sql::q($id[$i])));
                        }
                    }else{
                        $where->add(new sql_cond($keys[0], sql::EQUALS, sql::q($id[0])));
                    }
                    
                    /* Do we have a date_deleted field? */
                    if (in_array('date_deleted', $fields)){
                        /* We need to add the date deleted field */
                        $where->add(new sql_cond('date_deleted', sql::IS, new sql_null()));
                    }
                    
                    /* Add the where clause */
                    $sql->where($where);
                    
                    /* Execute the query and get the results */
                    $results = $sql->execute(0)->results();
                    
                    /* Make sure we have results */
                    if (isset($results) && is_array($results)){
                        
                        if (count($results) == 0){
                            $this->error('Unable to load record, record not found.');
                        }elseif(count($results) == 1){
                            $this->trigger(self::EVENT_ON_LOAD);
                            return $this->load_by_data($results[0]);
                        }elseif(count($results) > 1){
                            $this->error("The database has successfully managed to return multiple results for the same primary key!");
                        }
                        
                    }else{
                        $this->error('Unable to load record');
                        $errors = $sql->errors(true);
                        foreach($errors as $error) $this->error("SQL Error: " . $error);
                    }
                    
                }else{
                    $this->error('Incorrect number of id\'s supplied to load table ' . $this->_table_name);
                }
                
            }else{
                $this->error("Unable to load, no id provided");
            }
            
            return false;
        }
        
        /**
         * Loads the model with the $name
         *
         * @access public
         * @param string
         * The name of the model to load
         * @return boolean
         * **true** is successful
         */
        public function load_by_name($name){
            $this->initialise();
            
            /* Make sure name is set */
            if (isset($name)){
                
                /* We need to check this table has a name field */
                $fields = array_keys($this->_data);
                
                if (in_array('name', $fields)){
                    $sql = $this->data_source->sql;
                    
                    $sql->select(new sql('*'))
                        ->from($this->table_name);
                    
                    /* Do we have a date_deleted field? */
                    if (in_array('date_deleted', $fields)){
                        
                        $name_condition = new sql_cond('name', sql::EQUALS, sql::q($name));
                        $date_deleted_condition = new sql_cond('date_deleted', sql::IS, new sql_null());
                        
                        $sql->where(new sql_and($name_condition, $date_deleted_condition));
                        
                    }else{
                        
                        $sql->where(new sql_cond('name', sql::EQUALS, sql::q($name)));
                    }
                    
                    /* Get the results */
                    $results = $sql->execute(0)->results();
                    
                    if (count($results) == 1){
                        $this->trigger(self::EVENT_ON_LOAD_BY_NAME);
                        return $this->load_by_data($results[0]);
                    }elseif(count($results) == 0){
                        $this->error("Unable to find a record named {$name}");
                    }elseif(count($results) > 1){
                        $this->error(count($results) . " records found named '{$name}'.");
                    }
                    
                }else{
                    $this->error('Unable to load by name, this table has no \'name\' field.');
                }
            }else{
                $this->error('Unable to load by name, no name supplied');
            }
            
            return false;
        }
        
        /**
         * Loads the model with the supplied guid
         *
         * @access public
         * @param string
         * The GUID
         * @return boolean
         * **true** is successful
         */
        public function load_by_guid($guid){
            $this->initialise();
            
            /* Make sure name is set */
            if (isset($guid)){
                
                /* We need to check this table has a guid field */
                $fields = array_keys($this->_data);
                
                if (in_array('guid', $fields)){
                    $sql = $this->data_source->sql;
                    
                    $sql->select(new sql('*'))
                        ->from($this->table_name);
                    
                    /* Do we have a date_deleted field? */
                    if (in_array('date_deleted', $fields)){
                        
                        $name_condition = new sql_cond('guid', sql::EQUALS, sql::q($guid));
                        $date_deleted_condition = new sql_cond('date_deleted', sql::IS, new sql_null());
                        
                        $sql->where(new sql_and($name_condition, $date_deleted_condition));
                        
                    }else{
                        
                        $sql->where(new sql_cond('guid', sql::EQUALS, sql::q($guid)));
                    }
                    
                    /* Get the results */
                    $results = $sql->execute(0)->results();
                    
                    if (count($results) == 1){
                        $this->trigger(self::EVENT_ON_LOAD_BY_GUID);
                        return $this->load_by_data($results[0]);
                    }elseif(count($results) == 0){
                        $this->error("Unable to find a record with guid {$guid}");
                    }elseif(count($results) > 1){
                        $this->error(count($results) . " records found with guid '{$guid}'.");
                    }
                    
                }else{
                    $this->error('Unable to load by guid, this table has no \'guid\' field.');
                }
            }else{
                $this->error('Unable to load by guid, no guid supplied');
            }
            
            return false;
        }
        
        /**
         * Loads the model with the supplied $data
         *
         * @access public
         * @param array
         * An associative array with the data for this model
         * @return boolean
         * **true** is successful
         */
        public function load_by_data($data = array()){
            $this->initialise();
            
            foreach(array_keys($this->_data) as $key){
                if (isset($data[$key])){
                    $this->_data[$key] = $data[$key];
                }
            }
            
            /* We need to check the primary keys are set */
            $keys = $this->data_source->get_primary_keys($this->_table_name);
            $keys_set = true;
            
            foreach($keys as $key){
                if (!isset($this->_data[$key])){
                    $keys_set = false;
                }
            }
            
            if ($keys_set){
                $this->_is_loaded = true;
                
                /* Load children */
                if ($this->_auto_load_children == true){
                    /* Get a copy of the full schema */
                    $tables = $this->_auto_load_only_tables;
                    
                    foreach($tables as $table){
                        $relationship = $this->data_source->get_relationship($this->table_name, $table);
                        
                        $sql = $this->data_source->sql;
                        $sql->select('*');
                        $sql->from($table);
                        
                        if (is_array($relationship)){
                            $field_key = $relationship['field1'];
                            $where_sql = new sql_cond($relationship['field2'], sql::EQUALS, sql::q($this->$field_key));
                        }else{
                            $key = $keys[0];
                            $where_sql = new sql_cond($key, sql::EQUALS, sql::q($this->$key));
                        }
                        
                        /* Do we have a date_deleted field? */
                        $date_deleted = $this->data_source->get_field_structure($table, 'date_deleted');
                        
                        if (is_array($date_deleted) && count($date_deleted) > 0){
                            
                            /* We need to add the date deleted field */
                            $where_sql = new sql_and($where_sql, new sql_cond('date_deleted', sql::IS, new sql_null()));
                        }
                        
                        $sql->where($where_sql);
                        
                        /* Do we have a priority field? */
                        $priority = $this->data_source->get_field_structure($table, 'priority');
                        
                        if (is_array($priority) && count($priority) > 0){
                            $sql->order_by('priority');
                        }
                        
                        /* Execute the statement */
                        if ($sql->execute(0)){
                            $results = $sql->results();
                            
                            /* Load the models */
                            foreach($results as $result){
                                $model = "model_" . $table;
                                
                                if (class_exists($model)){
                                    $model = new $model();
                                    if ($model instanceof model){
                                        if ($model->load_by_data($result)){
                                            /* Add the child */
                                            $this->add($model);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                /* Check we have permission to load */
                if (!method_exists($this, 'permission_load') || $this->permission_load()){
                    /* Fire the EVENT_ON_LOAD_BY_DATA event */
                    $this->trigger(self::EVENT_ON_LOAD_BY_DATA);
                    return true;
                }else{
                    /* Not permissioned to use this object */
                    $this->initialise();
                    $this->error("You are not permitted to load this model");
                    return false;
                }
            }else{
                $this->error("Failed to load by data due to missing keys");
                $this->initialise();
                return false;
            }
        }
        
        /**
         * Static function to load many models at a time
         *
         * @access public
         * @param string
         * The table name to get the data from
         * @param integer[]
         * An array of ID's to load
         * @return model[]
         * An array of models
         */
        public static function load_many($table_name, $ids = array()){
            /* The array we are going to output */
            $output = array();
            
            /* Get a reference to the global adapt object because
             * we are not in an instance
             */
            $adapt = $GLOBALS['adapt'];
            
            /* Lets ensure $adapt is set and we have ids */
            if (is_array($ids) && count($ids) >= 1 && is_object($adapt) && $adapt instanceof base){
                
                /* Does this table exist? */
                $table_structure = $adapt->data_source->get_row_structure($table_name);
                
                if (is_array($table_structure) && count($table_structure)){
                    
                    /* Get the primary key name(s) */
                    $keys = $adapt->data_source->get_primary_keys($table_name);
                    
                    /* Get a new sql object */
                    $sql = $adapt->data_source->sql;
                    
                    /* Select all fields */
                    $sql->select(new sql('*'));
                    
                    /* From the table */
                    $sql->from($table_name);
                    
                    /* Are we loading one or many? */
                    $where_sql = null;
                    if (count($ids) == 1){
                        
                        /* Does the key count match the id count */
                        if (!is_array($ids[0])) $ids[0] = array($ids[0]); //Just to make it easier!
                        if (count($ids[0]) == count($keys)){
                            
                            /* If there are multiple keys we need to use a sql_and */
                            if (count($keys) > 1){
                                $and = new sql_and();
                                for($i = 0; $i < count($keys); $i++){
                                    $and->add(new sql_cond($keys[$i], sql::EQUALS, sql::q($ids[$i])));
                                }
                                
                                $where_sql = $and;
                                
                            }else{
                                $where_sql = new sql_cond($keys[0], sql::EQUALS, sql::q($ids[0][0]));
                            }
                            
                        }
                        
                        
                    }else{
                        
                        /* We are loading many */
                        $where_sql = new sql_or();
                        
                        foreach($ids as $id){
                            if (!is_array($id)) $id = array($id);
                            
                            if (count($id) == count($keys)){
                                
                                if (count($keys) == 1){
                                    $where_sql->add(new sql_cond($keys[0], sql::EQUALS, sql::q($id[0])));
                                }else{
                                    $and = new sql_and();
                                    for($i = 0; $i < count($keys); $i++){
                                        $and->add(new sql_cond($keys[$i], sql::EQUALS, sql::q($id[$i])));
                                    }
                                    
                                    $where_sql->add($and);
                                }
                                
                            }
                        }
                        
                    }
                    
                    /* Do we have a date_deleted field? */
                    $date_deleted = $adapt->data_source->get_field_structure($table_name, 'date_deleted');
                    
                    if (is_array($date_deleted) && count($date_deleted) > 0){
                        
                        /* We need to add the date deleted field */
                        $where_sql = new sql_and($where_sql, new sql_cond('date_deleted', sql::IS, new sql_null()));
                    }
                    
                    /* Add the where clause */
                    $sql->where($where_sql);
                    
                    /* Get the results */
                    $results = $sql->execute(0)->results();
                    
                    /* Do we have any results? */
                    if (is_array($results) && count($results)){
                        
                        /* Set the model name */
                        $model_name = "model_{$table_name}";
                        
                        /* For each of the results load the model */
                        foreach($results as $result){
                            $model = base::create_object($model_name);
                            
                            if ($model instanceof $model_name){
                                $model->load_by_data($result);
                                
                                if ($model->is_loaded){
                                    $output[] = $model;
                                }
                            }
                        }
                        
                    }
                }
                
            }
            
            return $output;
        }
        
        /**
         * Saves the data to the data source
         *
         * @access public
         * @return boolean|integer
         * Saving an existing model returns true or false, saving a new
         * model returns it's ID if successful otherwise false.
         */
        public function save(){
            /*
             * Check we are permitted to save this object
             */
            if (method_exists($this, 'permission_save') && !$this->permission_save()){
                $this->error("You are not permitted to save this model");
                return false;
            }
            
            $return = false;
            /*
             * Before we do anything we need to check if this object
             * is valid
             */

            if ($this->is_valid){
                $return = true;
                $children = $this->get();

                /*
                 * We need to save any children that we
                 * depend up on
                 */
                $fields = array_keys($this->_data);
                $keys = $this->data_source->get_primary_keys($this->table_name);
                
                foreach($fields as $field){
                    $reference = $this->data_source->get_reference($this->table_name, $field);
                    
                    if ($reference && is_array($reference) && count($reference)){
                        foreach($children as $child){
                            if ($child instanceof model && $child->table_name == $reference['table_name']){
                                if (is_null($this->$field)){
                                    // Only if the child isn't loaded
                                    if (!$child->is_loaded){
                                        if ($child->save()){
                                            $child_id_field = $reference['field_name'];
                                            $this->$field = $child->$child_id_field;
                                        }else{
                                            $errors = $child->errors(true);
                                            foreach($errors as $error) $this->error($error);
                                            $return = false;
                                        }
                                    }else{
                                        $child_id_field = $reference['field_name'];
                                        $this->$field = $child->$child_id_field;
                                    }
                                    
                                }else{
                                    // Match the child and save it
                                    $child_id_field = $reference['field_name'];
                                    if ($child->$child_id_field == $this->$field){
                                        if (!$child->save()){
                                            $errors = $child->errors(true);
                                            foreach($errors as $error) $this->error($error);
                                            $return = false;
                                        }
                                    }
                                }
                                
                            }
                        }
                    }
                }
                
                if ($return == true){
                    /*
                     * Has anything changed?
                     */

                    if ($this->has_changed == true){
                        /* Lets build the sql statement */
                        $sql = $this->data_source->sql; //Same as: $sql = new sql(null, $this->data_source);
                        
                        /* Does this table have a date_modified field? */
                        if (in_array('date_modified', $fields)){
                            $this->_changed_fields['date_modified'] = [
                                'old_value' => $this->_data['date_modified'],
                                'new_value' => new sql_now()
                            ];
                            $this->_data['date_modified'] = new sql_now();
                        }
                        
                        $data_to_write = array();
                        
                        foreach($this->_changed_fields as $field_name => $values){
                            /* Make sure we are not writing a key */
                            if (!in_array($field_name, $keys)){
                                $data_to_write[$field_name] = $values['new_value'];
                            }
                        }

                        if ($this->is_loaded){
                            /*
                             * We are going to update an existing
                             * record
                             */
                            
                            /* Check our primary keys are set */
                            $null = new sql_null();
                            foreach($keys as $key){
                                if (
                                    is_null($this->_data[$key]) || $this->_data[$key] == ""
                                    || ($this->_data[$key] instanceof sql && $this->_data[$key]->render() == $null->render())
                                ){
                                    $this->error("Failed to save, missing primary key ({$key})");
                                    $return = false;
                                }
                            }
                            
                            if ($return == true){
                                
                                /* Do we have data to write? */
                                if (count($data_to_write) > 0){
                                    $sql->update($this->table_name);
                                    
                                    foreach($data_to_write as $key => $value){
                                        if ($value instanceof sql){
                                            $sql->set($key, $value);
                                        }else{
                                            $sql->set($key, sql::q($value));
                                        }
                                    }
                                    
                                    if (count($keys) == 1){
                                        $sql->where(new sql_cond($keys[0], sql::EQUALS, sql::q($this->_data[$keys[0]])));
                                    }elseif(count($keys) > 1){
                                        $where = new sql_and();
                                        foreach($keys as $key){
                                            $where->add(new sql_cond($key, sql::EQUALS, sql::q($this->_data[$key])));
                                        }
                                        
                                        $sql->where($where);
                                    }
                                    
                                    /* Write to the database */
                                    $sql->execute();
                                    
                                    /* Check for errors */
                                    $errors = $sql->errors(true);
                                    if (count($errors) > 0){
                                        
                                        /* Found errors :( */
                                        $this->error("Failed to save");
                                        foreach($errors as $error){
                                            $this->error($error);
                                            $return = false;
                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }else{
                            /*
                             * We are going to create a new record
                             * from scratch
                             */
                            
                            /* Does this table have a guid field? */
                            if (in_array('guid', $fields) && is_null($this->guid)){
                                $this->_data['guid'] = guid();
                            }
                            
                            /* Do we have an auto_increment field? */
                            $auto_increment_field = null;
                            foreach($keys as $key){
                                $structure = $this->data_source->get_field_structure($this->table_name, $key);
                                
                                if ($structure['primary_key'] == 'Yes'){
                                    $auto_increment_field = $key;
                                }
                            }
                            
                            /* Because this is a new record we are going
                             * to write every field so that the defaults
                             * are set.
                             */
                            $data_to_write = array();
                            foreach($fields as $field){
                                if ($field != $auto_increment_field){
                                    $data_to_write[$field] = $this->_data[$field];
                                }
                            }
                            
                            /* Build the sql statement */
                            $sql->insert_into($this->table_name, array_keys($data_to_write));
                            $sql->values(array_values($data_to_write));

                            /* Execute the statement */
                            $sql->execute();
                            
                            /* Did the sql statement succeed? */
                            $errors = $sql->errors(true);
                            
                            if (count($errors) > 0){
                                /* Found errors :( */
                                $this->error("Failed to save");
                                foreach($errors as $error){
                                    $this->error($error);
                                    $return = false;
                                }
                            }elseif(!is_null($auto_increment_field)){
                                /* Statement succeed so lets get the ID */
                                $this->_data[$auto_increment_field] = $sql->id();
                                if (is_null($this->_data[$auto_increment_field])){
                                    $this->errors("Failed to retrieve the insert ID from the database");
                                    $return = false;
                                }else{
                                    /* We have an ID so we are going to use it as our return value */
                                    $return = $this->_data[$auto_increment_field];
                                }
                            }
                        }
                        
                        
                    }
                    
                    /*
                     * If we saved successfully then we need
                     * to save any children than depend on
                     * this model
                     */
                    if ($return !== false){
                        /* We need to check for false because it may now contain an ID */
                        
                        /* Save dependent children */
                        foreach($fields as $field_name){
                            $references = $this->data_source->get_referenced_by($this->table_name, $field_name);
                            
                            if (is_array($references) && count($references) > 0){
                                foreach($references as $ref){
                                    foreach($children as $child){
                                        if ($child instanceof model && $child->table_name == $ref['table_name']){
                                            $child_field_name = $ref['field_name'];
                                            $child->$child_field_name = $this->$field_name;
                                            if ($child->save() === false){
                                                $this->error("Saved but failed to save dependent child ({$child->table_name}).");
                                                /* Should we return true or false? */
                                                $return = false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        /* Clear _change_fields */
                        $this->_changed_fields = array();
                        
                        /* Set has_changed & is_loaded */
                        $this->_is_loaded = true;
                        $this->_has_changed = false;
                        
                    }
                }
                
            }
            
            if ($return == true){
                $this->trigger(self::EVENT_ON_SAVE);
            }
            
            /* Return */
            return $return;
        }
        
        /**
         * Deletes the model
         *
         * @access public
         */
        public function delete(){
            if ($this->is_loaded){
                if (!method_exists($this, 'permission_delete') || $this->permission_delete()){
                    $this->date_deleted = new sql_now();
                    $this->save();
                    $this->initialise();
                }else{
                    $this->error("You do not have permission to delete this model");
                }
            }
        }
        
        /**
         * Export hash to a hash array
         *
         * @access public
         * @return array
         * A hash array containing the models data.
         */
        public function to_hash(){
            $output = array();
            $hash = array();
            
            foreach($this->_data as $key => $value){
                if ($value instanceof sql){
                    $sql = new sql_null();
                    if ($sql->render() == $value->render()){
                        $hash[$key] = null;
                    }
                }elseif(is_null($value)){
                    $hash[$key] = null;
                }else{
                    $hash[$key] = $this->data_source->format($this->table_name, $key, $value);
                }
            }
            
            $class = new \ReflectionClass(get_class($this));
            foreach($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
                $name = $method->name;
                if (substr($name, 0, 5) == "mget_"){
                    $key = substr($name, 5);
                    $hash[$key] = $this->$name();
                }
            }
            
            // Look at extended properties
            $extensions = $this->store('adapt.extensions');

            foreach($extensions as $class_name => $data){
                $class_name = array_pop(explode("\\", $class_name));
                if ($class_name == array_pop(explode("\\", get_class($this)))){
                    $methods = array_keys($data);
                    foreach($methods as $method){
                        if (substr($method, 0, 5) == 'mget_'){
                            $hash[substr($method, 5)] = $this->$method();
                        }
                    }
                }
            }
            
            $output[$this->table_name] = $hash;
            
            $children = $this->get();
            foreach($children as $child){
                if ($child instanceof model){
                    $hash = $child->to_hash();
                    
                    foreach($hash as $table_name => $fields){
                        if (isset($output[$table_name])){
                            foreach($fields as $key => $values){
                                
                                if (is_array($values)){
                                    foreach($values as $value){
                                        if (!is_array($output[$table_name][$key])) $output[$table_name][$key] = array($output[$table_name][$key]);
                                        $output[$table_name][$key][] = $value;
                                    }
                                }else{
                                    if (!is_array($output[$table_name][$key])) $output[$table_name][$key] = array($output[$table_name][$key]);
                                    $output[$table_name][$key][] = $values;
                                }
                                
                                
                                //$output[$table_name][$key] = array_merge($output[$table_name][$key], $values);
                            }
                        }else{
                            $output[$table_name] = $hash[$table_name];
                        }
                    }
                    
                    //$output = array_merge($output, $hash);
                    //foreach($hash as $key => $value){
                    //    
                    //}
                    
                    //$output[$this->table_name][$child->table_name][] = $hash[$child->table_name];
                    //$output[$child->table_name][] = $hash[$child->table_name];
                    
                    //foreach($hash as $key => $values){
                    //    foreach($values as $value){
                    //        $output[$key][] = $value;
                    //    }
                    //}
                }
            }
            
            return $output;
        }
        
        /**
         * Returns a simplified hash array
         *
         * @access public
         * @return array
         * A hash array containing the models data.
         */
        public function to_hash_string(){
            $output = array();
            $hash = $this->to_hash();
            
            //foreach($hash as $table_name => $field){
            //    foreach($field as $field_name => $values){
            //        if (is_array($values)){
            //            foreach($values as $value){
            //                $key = "{$table_name}[{$field_name}][]";
            //                $output[] = array('key' => $key, 'value' => $value);
            //            }
            //        }else{
            //            $key = "{$table_name}[{$field_name}]";
            //            $output[] = array('key' => $key, 'value' => $values);
            //            //$output["{$table_name}[{$field_name}]"] = $values;
            //        }
            //    }
            //}
            //return $output;
            foreach($hash as $table => $values){
                
                foreach($values as $field => $value){
                    $key = "{$table}[$field]";
                    if (is_array($value)){
                        $key .= "[]";
                    }
                    
                    $output[$key] = $value;
                }
            }
            
            return $output;
        }
        
        /**
         * Returns the models data as XML
         *
         * @access public
         * @return xml
         * XML object containing the models data.
         */
        public function to_xml(){
            /* Create a new XML object */
            $xml = new xml($this->table_name, array('type' => 'model'));
            
            /* Lets add all the values from $this->_data */
            $keys = $this->data_source->get_primary_keys($this->table_name);
            
            foreach($this->_data as $name => $value){
                $field_details = $this->data_source->get_field_structure($this->table_name, $name);
                $references = $this->data_source->get_reference($this->table_name, $name);
                $data_type = $this->data_source->get_data_type($field_details['data_type_id']);
                
                /* Format the value as required */
                if (!is_null($value)){
                    $value = $this->data_source->format($this->table_name, $name, $value);
                }
                
                
                $node = new xml($name, $value, array('type' => $data_type['name']));
                
                if ($field_details['primary_key'] == "Yes"){
                    $node->attr('key', 'primary');
                }elseif(is_array($references) && count($references)){
                    $node->attr('key', 'foreign');
                    $node->attr('references_model', $references['table_name']);
                    $node->attr('references_field', $references['field_name']);
                }/*elseif($field_details['data_type'] == "datetime"){
                    $format = $this->setting('format.datetime');
                    if (!is_null($format)){
                        $node->attr('type', $format);
                    }
                }elseif($field_details['data_type'] == "date"){
                    $format = $this->setting('format.date');
                    if (!is_null($format)){
                        $node->attr('type', $format);
                    }
                }elseif($field_details['data_type'] == "time"){
                    $format = $this->setting('format.time');
                    if (!is_null($format)){
                        $node->attr('type', $format);
                    }
                }elseif($field_details['data_type'] == "timestamp"){
                    $node->attr('timestamp', 'Yes');
                    $format = $this->setting('format.datetime');
                    if (!is_null($format)){
                        $node->attr('type', $format);
                    }else{
                        $node->attr('type', 'datetime');
                    }
                    
                }*/
                
                $xml->add($node);
            }
            
            /* We need to add any mget_'s */
            $class = new \ReflectionClass(get_class($this));
            foreach($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
                $name = $method->name;
                if (substr($name, 0, 5) == "mget_"){
                    $xml->add(new xml(substr($name, 5), $this->$name(), array('type' => 'dynamic_property')));
                }
            }
            
            /* Lets add any children we have */
            $children = $this->get();
            foreach($children as $child){
                if ($child instanceof model){
                    $xml->add($child->to_xml());
                }
            }
            
            return $xml;
        }
        
        /**
         * Returns the models data in JSON format
         *
         * @access public
         * @return string
         * A string containing the data in JSON format.
         */
        public function to_json(){
            $output = [];
            foreach($this->_data as $key => $value){
                if ($value instanceof sql){
                    $sql = new sql_null();
                    if ($sql->render() == $value->render()){
                        $hash[$key] = null;
                    }
                }elseif(is_null($value)){
                    $hash[$key] = null;
                }else{
                    $hash[$key] = $this->data_source->format($this->table_name, $key, $value);
                }
            }
            
            $class = new \ReflectionClass(get_class($this));
            foreach($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
                $name = $method->name;
                if (substr($name, 0, 5) == "mget_"){
                    $key = substr($name, 5);
                    $hash[$key] = $this->$name();
                }
            }
            
            $output[$this->table_name] = $hash;
            
            $children = $this->get();
            
            foreach($children as $child){
                if ($child instanceof model){
                    if (!isset($output[$child->table_name])){
                        $output[$child->table_name] = [];
                    }
                    
                    $hash = $child->to_hash();
                    $output[$child->table_name][] = $hash[$child->table_name];
                }
            }
            
            return json_encode($output);
        }
        
        /** @ignore */
        public function __toString(){
            return $this->to_json();
        }
        
        /**
         * Pushes data into the model
         *
         * @access public
         * @param string|array|xml
         * The data in XML, a hash array or in JSON format.
         */
        public function push($data){
            if (is_array($data) && is_assoc($data) && count($data)){
                /*
                 * This should be a hash
                 */
                $table_names = array_keys($data);
                $keys = $this->data_source->get_primary_keys($this->table_name);
                
                /* Lets fix the array so everything has multiple recrods */
                for($i = 0; $i < count($table_names); $i++){
                    $table_name = $table_names[$i];
                    $fields = array_keys($data[$table_name]);
                    
                    if (is_array($fields)){
                        for($j = 0; $j < count($fields); $j++){
                            $field_name = $fields[$j];
                            if (!is_array($data[$table_name][$field_name])){
                                $data[$table_name][$field_name] = array($data[$table_name][$field_name]);
                            }
                        }
                    }
                }
                
                /* Do we have a record? */
                if (isset($data[$this->table_name]) && is_array($data[$this->table_name])){
                    
                    /* How many records do we have? */
                    $record_count = 0;
                    $record_processed = null;
                    $field_names = array_keys($data[$this->table_name]);
                    $record_count = count($data[$this->table_name][$field_names[0]]);
                    
                    
                    
                    for($i = 0; $i < $record_count; $i++){
                        
                        if (is_null($record_processed)){
                            if ($this->is_loaded){
                                /* Only if the keys match */
                                $keys_required = count($keys);
                                
                                foreach($keys as $key){
                                    if ($data[$this->table_name][$key][$i] == $this->$key){
                                        $keys_required--;
                                    }
                                }
                                
                                if ($keys_required == 0){
                                    /* We can accept */
                                    $record_processed = $i;
                                    foreach($field_names as $field){
                                        $this->$field = $data[$this->table_name][$field][$i];
                                    }
                                }
                                
                            }else{
                                /* Only if there are no keys */
                                $keys_required = count($keys);
                                
                                foreach($keys as $key){
                                    if (!is_null($data[$this->table_name][$key][$i]) && $data[$this->table_name][$key][$i] != '' && $data[$this->table_name][$key][$i] == $this->$key){
                                        $keys_required--;
                                    }
                                }
                                
                                if ($keys_required == count($keys)){
                                    /* We can accept */
                                    $record_processed = $i;
                                    foreach($field_names as $field){
                                        $this->$field = $data[$this->table_name][$field][$i];
                                    }
                                }else{
                                    /* Load the record */
                                    $ids = array();
                                    foreach($keys as $key){
                                        $ids = $data[$this->table_name][$key][$i];
                                    }
                                    
                                    if ($this->load($ids)){
                                        $record_processed = $i;
                                        foreach($field_names as $field){
                                            $this->$field = $data[$this->table_name][$field][$i];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!is_null($record_processed)){
                        /* We need to remove this record from the data array */
                        $temp_array = array();
                        foreach($field_names as $field){
                            $temp_array[$field] = array();
                            
                            for($i = 0; $i < $record_count; $i++){
                                if ($i != $record_processed){
                                    $temp_array[$field][] = $data[$this->table_name][$field][$i];
                                }
                            }
                        }
                        
                        $data[$this->table_name] = $temp_array;
                    }
                    
                }
                
                /* Do we have anything for our children? */
                foreach($table_names as $table_name){
                    if ($this->table_name != $table_name && in_array($table_name, $this->_auto_load_only_tables)){
                        
                        if (is_array($this->data_source->get_relationship($this->table_name, $table_name))){
                            $field_names = array_keys($data[$table_name]);
                            $record_count = count($data[$table_name][$field_names[0]]);
                            $keys = $this->data_source->get_primary_keys($table_name);
                            
                            for($i = 0; $i < $record_count; $i++){
                                
                                $record = array($table_name => array());
                                foreach($field_names as $field_name){
                                    $record[$table_name][$field_name] = $data[$table_name][$field_name][$i];
                                }
                                
                                $child_found = false;
                                $children = $this->get();
                                $record_processed  = false;
                                
                                foreach($children as $child){
                                    if ($child instanceof model && $child->table_name == $table_name){
                                        if ($child->is_loaded){
                                            
                                            /* Only push if the keys match */
                                            $keys_required = count($keys);
                                            foreach($keys as $key){
                                                if ($this->$key == $record[$table_name][$key]) $keys_required--;
                                            }
                                            
                                            if ($keys_required == 0){
                                                /* We can push */
                                                $hash = $data;
                                                
                                                /* Remove all siblings from the push hash */
                                                $hash[$table_name] = $record[$table_name];
                                                
                                                /* Push the data */
                                                $child->push($hash);
                                                
                                                /* Mark the record as processed */
                                                $record_processed = true;
                                            }
                                            
                                            
                                        }else{
                                            //print "bar\n";
                                            ///* Only push if there are no keys */
                                            //$keys_required = count($keys);
                                            //foreach($keys as $key){
                                            //    if (isset($record[$table_name][$key]) && $record[$table_name][$key] != "") $keys_required--;
                                            //}
                                            //
                                            //if ($keys_required == count($keys)){
                                            //    /* We can push */
                                            //    $hash = $data;
                                            //    
                                            //    /* Remove all siblings from the push hash */
                                            //    $hash[$table_name] = $record[$table_name];
                                            //    
                                            //    /* Push the data */
                                            //    $child->push($hash);
                                            //    
                                            //    /* Mark the record as processed */
                                            //    $record_processed = true;
                                            //}
                                        }
                                    }
                                    
                                }
                                
                                if (!$record_processed){
                                    /* Add child */
                                    $model_name = "model_" . $table_name;
                                    $model = new $model_name();
                                    
                                    $hash = $data;
                                    
                                    /* Remove all siblings from the push hash */
                                    $hash[$table_name] = $record[$table_name];
                                    
                                    /* Push the data */
                                    $model->push($hash);
                                    
                                    /* Add the model */
                                    $this->add($model);
                                    
                                    /* Mark the record as processed */
                                    $record_processed = true;
                                }
                            }
                            
                        }
                    }
                }
                
                
                
                
                /*****/
                
                
                /*
                 * This could be a hash or a hash string,
                 * if it's a hash_string we are going to
                 * convert it to a hash first
                 */
                //$keys = array_keys($data);
                //
                ///* Is this a hash_string? */
                //if (preg_match("/^[-_a-zA-Z0-9]+\[[0-9]+\]\[[-_A-Za-z0-9]+\]$/", $keys[0])){
                //    $hash = array();
                //    
                //    foreach($keys as $key){
                //        $matches = array();
                //        if (preg_match_all("/^([-_a-zA-Z0-9]+)\[([0-9]+)\]\[([-_A-Za-z0-9]+)\]$/", $key, $matches)){
                //            $hash[$matches[1][0]][$matches[2][0]][$matches[3][0]] = $data[$key];
                //        }
                //    }
                //    
                //    $data = $hash;
                //}
                //
                ///* Lets process the hash */
                //$tables = array_keys($data);
                //$processed = false;
                //
                //foreach($tables as $table){
                //    if ($table == $this->table_name && !$processed){
                //        $records = $data[$table];
                //        $formatted_array = array();
                //        
                //        $record_keys = array_keys($records);
                //        
                //        if (is_array($records[$record_keys[0]])){
                //            for($i = 0; $i < count($records[$record_keys[0]]); $i++){
                //                $new_record = array();
                //                foreach($record_keys as $field_name){
                //                    $new_record[$field_name] = $records[$field_name][$i];
                //                }
                //                $formatted_array[] = $new_record;
                //            }
                //        }else{
                //            $new_record = array();
                //            foreach($record_keys as $field_name){
                //                $new_record[$field_name] = $records[$field_name];
                //            }
                //            $formatted_array[] = $new_record;
                //        }
                //        
                //        $records = $formatted_array;
                //        
                //        for($i = 0; $i < count($records); $i++){
                //            $record = $records[$i];
                //            if ($this->is_loaded){
                //                /* We need to make sure this record is ours */
                //                $primary_keys = $this->data_source->get_primary_keys($this->table_name);
                //                $valid = true;
                //                foreach($primary_keys as $key){
                //                    if (!isset($record[$key]) || $record[$key] != $this->$key){
                //                        $valid = false;
                //                    }
                //                }
                //                
                //                if ($valid){
                //                    /* Lets push this record */
                //                    foreach($record as $key => $value){
                //                        $this->$key = $value;
                //                    }
                //                    $processed = true;
                //                    $data[$table] = array_remove($data[$table], $i);
                //                }
                //            }else{
                //                /*
                //                 * We are not loaded so we will process the first available
                //                 * record that has no primary keys set
                //                 */
                //                $primary_keys = $this->data_source->get_primary_keys($this->table_name);
                //                $primary_key_values = array();
                //                $has_keys = true;
                //                foreach($primary_keys as $key){
                //                    if (!isset($record[$key]) || !is_null($record[$key])){
                //                        $has_keys = false;
                //                    }
                //                    $primary_key_values[] = $record[$key];
                //                }
                //                
                //                if ($has_keys){
                //                    /* We are going to load this record */
                //                    $this->load($primary_key_values);
                //                }
                //                
                //                /* Lets push the data in */
                //                foreach($record as $key => $value){
                //                    $this->$key = $value;
                //                }
                //                $processed = true;
                //                $data[$table] = array_remove($data[$table], $i);
                //            }
                //        }
                //    }
                //}
                //
                ///* Pass the data to any child objects we hold */
                //$children = $this->get();
                //foreach($children as $child){
                //    if ($child instanceof model){
                //        $child->push($data);
                //    }
                //}
                
            }elseif((is_object($data) && $data instanceof xml) || (is_string($data) && xml::is_xml($data))){
                /*
                 * Working with XML
                 */
                $xml = null;
                if ($data instanceof xml){
                    $xml = $data;
                }else{
                    $xml = xml::parse($data);
                }
                
                /* Is this XML feed for this object? */
                if ($xml->tag == $this->table_name){
                    /*
                     * Are we loaded and do the keys match?
                     */
                    
                    if ($this->is_loaded){
                        $keys = $this->data_source->get_primary_key($this->table_name);
                        
                        $valid = true;
                        foreach($keys as $key){
                            if ($xml->find($key)->text() != $this->$key){
                                $valid = false;
                            }
                        }
                        
                        if ($valid){
                            /* Lets push the data */
                            $nodes = $xml->get();
                            foreach($nodes as $node){
                                $key = $node->tag;
                                $value = $node->value();
                                $type = $node->attr('type');
                                if ($type == 'model'){
                                    /* Push down to children */
                                    $children = $this->get();
                                    foreach($children as $child){
                                        if ($child instanceof model){
                                            $child->push($node);
                                        }
                                    }
                                }else{
                                    /* Set the value */
                                    $this->$key = $value;
                                }
                            }
                        }
                    }else{
                        /*
                         * Because we are unloaded we are going
                         * to process the first available record that
                         * has no keys
                         */
                        $primary_keys = $this->data_source->get_primary_keys($this->table_name);
                        $primary_key_values = array();
                        $has_keys = true;
                        foreach($primary_keys as $key){
                            if ($xml->find($key)->text() == "" || is_null($xml->find($key)->text())){
                                $has_keys = false;
                            }
                            $primary_key_values[] = $xml->find($key)->text();
                        }
                        
                        if ($has_keys){
                            /* We are going to load this record */
                            $this->load($primary_key_values);
                        }
                        
                        /* Lets push the data in */
                        $nodes = $xml->get();
                        foreach($nodes as $node){
                            $key = $node->tag;
                            $value = $node->value();
                            $this->$key = $value;
                            $type = $node->attr('type');
                            if ($type == 'model'){
                                /* Push down to children */
                                $children = $this->get();
                                foreach($children as $child){
                                    if ($child instanceof model){
                                        $child->push($node);
                                    }
                                }
                            }else{
                                /* Set the value */
                                $this->$key = $value;
                            }
                        }
                    }
                }
            }elseif(is_json($data)){
                $data = json_decode($data, true);
                if (is_array($data) && is_assoc($data)){
                    /*
                     * After decoding the data should be
                     * the same as a hash, so we are just
                     * going to go ahead and re-push the
                     * hash
                     */
                    $this->push($data);
                }
            }
            $this->trigger(self::EVENT_ON_PUSH, array('data' => $data));
            return;
            
            /*
             * Push supports data in a number of formats
             * Format 1
             * string key: model[field][n]
             *             model[field][n...]
             *
             * Format 2
             * array: array (
             *          model => array(
             *              field => value...
             *          ),
             *          model => array(
             *              array(
             *                  field => value
             *              )
             *          )    
             *      )
             *
             * Format 3
             * xml
             *
             * Format 4
             * json
             *
             *
             *
             *
             *
             *
             */
            
            
            if (is_array($data) && is_assoc($data)){
                
                /*
                 * Push from a hash array
                 */
                
                /* Create an array to keep a record of all the models we find */
                $models = array();
                
                /* Get the keys for this table and prefix them with the table name */
                $keys = $this->data_source->get_primary_keys($this->table_name);
                if (is_null($keys)) $keys = array();
                
                for($i = 0; $i < count($keys); $i++){
                    $keys[$i] = $this->table_name . "-" . $keys[$i];
                }
                
                /* If all the keys are in the supplied data we can verify if
                 * its for this model or for another.  We we aren't loaded we
                 * can go ahead and load ourself
                 */
                $has_all_keys = true;
                $has_any_keys = false;
                $has_self_data = false;
                foreach($keys as $key){
                    if (!isset($data[$key])){
                        $has_all_keys = false;
                        $has_any_keys = true;
                    }
                }
                
                /* Proceed only if we have the right number of keys, or we have no keys */
                if(!$has_any_keys || $has_all_keys){
                    
                    if ($has_all_keys){
                        if ($this->is_loaded){
                            $hash = $this->hash();
                            $is_me = true;
                            foreach($keys as $key){
                                if ($hash[$key] != $data[$key]) $is_me = false; // :(
                            }
                            
                            //$has_self_data = $is_me;
                        }else{
                            /* Lets load ourself */
                            $values = array();
                            foreach($keys as $key){
                                $values[] = $data[$key];
                            }
                            $this->load($values);
                            //$has_self_data = true;
                        }
                    }/*else{
                        $has_self_data = true;
                    }*/
                    
                }
                
                /* Lets build an array of keys and values */
                foreach($data as $key => $value){
                    if (stripos($key, "-")){
                        list($table_name, $field) = explode("-", $key);
                        if (!in_array($table_name, array_keys($models))){
                            $models[$table_name] = array();
                        }
                        
                        if (is_array($value)){
                            for ($i = 0; $i < count($value); $i++){
                                $models[$table_name][$i][$field] = $value[$i];
                            }
                        }else{
                            $models[$table_name][0][$field] = $value;
                        }
                    }else{
                        $models[$this->table_name][0][$key] = $value;
                    }
                }
                
                
                
                //if ($has_self_data){
                    /* Push into ourself */
                    foreach(array_keys($models) as $table_name){
                        
                    }
                    
                //}
                
            }elseif((is_object($data) && $data instanceof xml) || (is_string($data) && xml::is_xml($data))){
                
                /*
                 * Push from xml
                 */
                
                
            }else/*if (<TEST FOR JSON>)*/{
                
                /*
                 * Push from JSON
                 */
                
            }
            
        }
    }

}
