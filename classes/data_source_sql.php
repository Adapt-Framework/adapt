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
     * Foundation class for building SQL data sources
     *
     * @property-read sql $sql
     * Read only property that provides a new SQL instance each time it's called.
     */
    abstract class data_source_sql extends data_source implements interfaces\data_source_sql{
        
        /**
         * Event that's fired when a host is connected
         */
        const EVENT_HOST_CONNECT = 'adapt.host_connect';
        
        /**
         * Event that's fired when a host is disconnected
         */
        const EVENT_HOST_DISCONNECT = 'adapt.host_disconnect';
        
        /**
         * An event that's fired when the data source is queried.
         */
        const EVENT_QUERY = 'adapt.query';
        
        /**
         * Used by the method fetch to denote the data should be returned
         * as an associative array
         */
        const FETCH_ASSOC = 1;
        
        /**
         * Used by the method fetch to denote the data should be returned
         * as an array
         */
        const FETCH_ARRAY = 2;
        
        /**
         * Used by the method fetch to denote the data should be returned
         * as an standard object
         */
        const FETCH_OBJECT = 3;
        
        /**
         * Used by the method fetch to denote all the data should be returned
         * as an array of associative arrays
         */
        const FETCH_ALL_ASSOC = 4;
        
        /**
         * Used by the method fetch to denote all the data should be returned
         * as an array of arrays
         */
        const FETCH_ALL_ARRAY = 5;
        
        /**
         * Used by the method fetch to denote all the data should be returned
         * as an array of standard objects
         */
        const FETCH_ALL_OBJECT = 6;
        
        /** @ignore */
        protected $_hosts;
        
        /** @ignore */
        protected $_read_host;
        
        /** @ignore */
        protected $_write_host;
        
        /**
         * Constructor
         *
         * When contructing with the params a new host is added.
         *
         * @access public
         * @param string
         * The hostname or IP address of the SQL server
         * @param string
         * The username for the SQL server
         * @param string
         * The password for the username for the SQL server.
         * @param string
         * The database name
         * @param string
         * The database port
         * @param boolean
         * Should the host be treated as read-only?  This is useful for Master/slave
         * database set ups.
         */
        public function __construct($host = null, $username = null, $password = null, $schema = null, $port = null, $read_only = false){
            parent::__construct();
            
            /* Kill the schema */
            $this->_schema = null;
            $this->_data_types = null;
            
            /* Set the hosts */
            $this->_hosts = array();
            
            if (!is_null($host) && !is_null($username) && !is_null($password) && !is_null($schema)){
                $this->add_host($host, $username, $password, $schema, $port, $read_only);
                $this->load_schema();
            }
        }
        
        /*
         * Properties
         */
        /** @ignore */
        public function pget_schema(){
            if (!is_array($this->_schema)){
                $this->load_schema();
            }
            
            return parent::pget_schema();
        }
        
        /** @ignore */
        public function pget_data_types(){
            if (!is_array($this->_data_types)){
                $this->load_schema();
            }
            
            return parent::pget_data_types();
        }
        
        /** @ignore */
        public function pget_sql(){
            return new sql(null, $this);
        }
        
        /**
         * Returns a new sql object.
         *
         * @access public
         * @param string
         * Optionally a SQL query
         * @return sql
         */
        public function sql($statement = null){
            return new sql($statement, $this);
        }
        
        /**
         * Retrieve record count
         *
         * @access public
         * @param string
         * The dataset index or name.
         * @return integer
         */
        public function get_number_of_rows($table_name){
            $table_schema = $this->get_row_structure($table_name);
            if (!is_null($table_schema)){
                $sql = new sql(null, $this);
                
                $sql->select(array('c' => 'count(*)'))->from($table_name);
                
                //$sql->select(new sql('count(*)'), 'c')->from($table_name);
                if (isset($table_schema['date_deleted'])){
                    //$sql->where(new sql_condition('date_deleted', 'is not', new sql('null')));
                    $sql->where(new sql_cond('date_deleted', sql::IS, 'null'));
                }
                
                $results = $sql->execute()->results();
                return $results[0]['c'];
            }
        }
        
        /**
         * Retrieve record
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
        public function get($table_name, $row_offset, $number_of_rows = 1){
            $table_schema = $this->get_row_structure($table_name);
            if (!is_null($table_schema)){
                $sql = new sql(null, $this);
                //$sql->select(new sql('*'))->from($table_name)->limit($number_of_rows, $row_offset);
                //if (isset($table_schema['date_deleted'])){
                
                $sql->select("*")->from($table_name)->limit($number_of_rows, $row_offset);
                
                if (!is_null($this->get_field_structure($table_name, 'date_deleted'))){
                    //$sql->where(new sql_condition('date_deleted', 'is not', new sql('null')));
                    $sql->where(new sql_cond('date_deleted', sql::IS, 'null'));
                }
                
                return $sql->execute()->results();
            }
        }
        
        /**
         * Returns an array of primary keys for a particular
         * table.
         *
         * @access public
         * @param string
         * The table name you wish to get the keys for.
         * @return string[]
         * An array of field names.
         */
        public function get_primary_keys($table_name){
            $keys = array();
            
            $fields = $this->get_row_structure($table_name);
            foreach($fields as $field){
                if ($field['primary_key'] == 'Yes') $keys[] = $field['field_name'];
            }
            
            return $keys;
        }
        
        /**
         * Execute a SQL write statement.
         *
         * @access public
         * @param string
         * A SQL statement such as INSERT, UPDATE or DELETE.
         * @return resource
         * Returns a statement handle.
         */
        public function write($sql){
            return $this->query($sql, true);
        }
        
        /**
         * Execute a SQL read statement.
         *
         * @access public
         * @param string
         * A SQL statement such as SELECT.
         * @return resource
         * Returns a statement handle.
         */
        public function read($sql){
            return $this->query($sql);
        }
        
        /**
         * Execute a SQL read or write statement.
         *
         * This is a placeholder function to be over-riden
         * by children inheriting from this class.
         *
         * @access public
         * @param string
         * A SQL statement.
         * @param boolean
         * Is this statement writing data?
         * @return resource
         * Returns a statement handle.
         */
        public function query($sql, $write = false){
            
        }
        
        /**
         * Fetches data from a statement handle.
         *
         * This is a placeholder function to be over-riden
         * by children inheriting from this class.
         *
         * @access public
         * @param resource
         * The statement handle returned from read(), write() or query().
         * @param integer
         * How should the data be fetched? See the constants prefixed FETCH_
         */
        public function fetch($statement_handle, $fetch_type = self::FETCH_ASSOC){
            
        }
        
        /**
         * Returns the last inserted record ID
         *
         * This is a placeholder function to be over-riden
         * by children inheriting from this class.
         *
         * @access public
         * @return integer
         * The ID of the record.
         */
        public function last_insert_id(){
            
        }
        
        /**
         * Adds a new host to the data source.
         *
         * Useful for working with Master/slave replication
         * or database clusters.
         *
         * @access public
         * @param string
         * The hostname or IP address of the SQL server
         * @param string
         * The username for the SQL server
         * @param string
         * The password for the username for the SQL server.
         * @param string
         * The database name
         * @param string
         * The database port
         * @param boolean
         * Should the host be treated as read-only?  This is useful for Master/slave
         * database set ups.
         */
        public function add_host($host, $username, $password, $schema, $port, $read_only = false){
            $this->_hosts[] = array(
                'host' => $host,
                'username' => $username,
                'password' => $password,
                'schema' => $schema,
                'port' => $port,
                'read_only' => $read_only
            );
            
        }
        
        /**
         * Opens a connection to the $host.
         *
         * Connections are handled automatically and you shouldn't
         * need to use this function.
         *
         * This is a placeholder function to be over-ridden by
         * children inheriting from this class.
         *
         * Example usage.
         * <code>
         * $source = new data_source_mysql();
         * $source->add_host('hostname', 'username', 'password', 'schema', true);
         * $source->connect($source->get_host(true));
         * </code>
         *
         * @access public
         * @param array
         * An array representing the host.
         */
        public function connect($host){
            
        }
        
        /**
         * Closes a connection to the $host.
         *
         * Connections are handled automatically and you shouldn't
         * need to use this function.
         *
         * This is a placeholder function to be over-ridden by
         * children inheriting from this class.
         *
         * Example usage.
         * <code>
         * $source = new data_source_mysql();
         * $source->add_host('hostname', 'username', 'password', 'schema', true);
         * $source->disconnect($source->get_host(true));
         * </code>
         *
         * @access public
         * @param array
         * An array representing the host.
         */
        public function disconnect($host){
            
        }
        
        /**
         * Returns a random host from the available hosts.
         *
         * This function is used for load balancing.
         *
         * @access public
         * @param boolean
         * Should the host be capable of writing?
         * @return array
         * An array representing the host.
         */
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
        
        /**
         * Converts a sql object to a SQL string for the target
         * database platform.
         *
         * This is a placeholder function to be over-ridden by
         * children inheriting from this class.
         *
         * @access public
         * @param sql
         * The sql object to be converted.
         * @return string
         * Returns the SQL statement as a string.
         */
        public function render_sql(sql $sql){
            
        }
        
        /**
         * Escapes a value
         *
         * This function should be overidden by the child
         * to ensure proper escaping on the target plaform.
         * This is only provided as a fall back for databases
         * that have no native escape function.
         *
         * @access public
         * @param string
         * The value to be escaped
         * @return string
         * The escaped value
         */
        public function escape($string){
            return addslashes($string);
        }
        
        /**
         * Validates a value
         *
         * @access public
         * @param string
         * The table the value is set to be stored in.
         * @param string
         * The field the value is set to be stored in.
         * @param string
         * The value to be validated.
         * @return boolean
         */
        public function validate($table_name, $field_name, $value){
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
                                $this->error("Maximum field size is {$max_length}");
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
        
        /**
         * Formats a value for display
         *
         * @access public
         * @param string
         * The table the value is set to be stored in.
         * @param string
         * The field the value is set to be stored in.
         * @param string
         * The value to be formatted.
         * @return string
         * Returns the formatted value
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
        
        /**
         * Unformats a value for storing
         *
         * @access public
         * @param string
         * The table the value is set to be stored in.
         * @param string
         * The field the value is set to be stored in.
         * @param string
         * The value to be unformatted.
         * @return string
         * Returns the unformatted value
         */
        public function unformat($table_name, $field_name, $value){
            if (is_object($value) && $value instanceof sql) return $value;
            //if ($table_name == 'vacancy'){
            //    print new html_pre("data_source_sql.unformat: {$field_name} set to '{$value}'");
            //}
            $field = $this->get_field_structure($table_name, $field_name);
            
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
        
        /**
         * Converts a data type from a string into an array
         *
         * This is a placeholder function to be over-ridden
         * by children inheriting from this class.
         * 
         * @access public
         * @param string
         * The data type as a string, such as 'varchar(64)'
         * @param boolean
         * For numeric data types, is the type signed?
         * @param boolean
         * Should the data type by zero filled?
         * @return array
         * Returns an array containing the data type structure.
         */
        public function convert_data_type($type, $signed = true, $zero_fill = false){
            
        }
        
        /**
         * Registers a table in the schema
         *
         * Each table in the schema must be registered for it to work with
         * the sql or model classes.  When defining tables in your bundle.xml
         * this call is automatically made for you.
         *
         * If you wish to add a table at any other time then you need to call
         * this function.
         *
         * $table_array must be an array of associative arrays with the following keys:
         * * **bundle_name** The name of the bundle registering the table
         * * **table_name** The name of the table
         * * **field_name** The name of the field
         * * **referenced_table_name** Does this field reference another table?
         * * **referenced_field_name** Does this field reference another field?
         * * **label** The label for this field
         * * **placeholder_label** A placeholder label for this field.
         * * **description** A description of this field
         * * **data_type_id** The data type id for this field
         * * **primary_key** Either **Yes** or **No**
         * * **signed** Is this field signed? Either **Yes** or **No**.
         * * **nullable** Is this field nullable? Either **Yes** or **No**
         * * **auto_increment** Should this field auto increment? Either **Yes** or **No**
         * * **timestamp** Is this a timestamp field? Either **Yes** or **No**
         * * **max_length** The max length of this field.
         * * **default_value** The default value for this field.
         * * **allowed_values** a JSON encoded array of allowed values
         * * **lookup_table** Does this field takes it's data from another table?
         * * **depends_on_table_name** Does this field depend on another field being a certain value?
         * * **depends_on_field_name** Does this field depend on another field being a certain value?
         * * **depends_on_value** Does this field depend on another field being a certain value?
         * * **date_created** The date this field was created, typically an instance of sql_null.
         *
         * @access public
         * @param array
         * An array of fields to be registered.
         * @return boolean
         * Returns **false** is unsuccessful, **true** is the registration succeeded.
         */ 
        public function register_table($table_array){
            $allowed_keys = array(
                'bundle_name', 'table_name', 'field_name', 'referenced_table_name',
                'referenced_field_name', 'label', 'placeholder_label', 'description',
                'data_type_id', 'primary_key', 'signed', 'nullable', 'auto_increment',
                'timestamp', 'max_length', 'default_value', 'allowed_values',
                'lookup_table', 'depends_on_table_name', 'depends_on_field_name',
                'depends_on_value', 'date_created'
            );
            
            if (is_array($table_array) && !is_assoc($table_array)){
                foreach($table_array as $field_array){
                    if (is_array($field_array) && is_assoc($field_array)){
                        $field_keys = array_keys($field_array);
                        
                        foreach($field_keys as $field_key){
                            if (!in_array($field_key, $allowed_keys)){
                                //print "<pre>register_table failed 1</pre>";
                                return false;
                            }
                        }
                    }else{
                        //print "<pre>register_table failed 2</pre>";
                        return false;
                    }
                }
            }else{
                //print "<pre>register_table failed 3</pre>";
                return false;
            }
            //print "<pre>Proceeding to register</pre>";
            
            $sql = $this->sql;
            $sql->insert_into('field', $allowed_keys);
            $schema = array();
            
            foreach($table_array as $field_array){
                $record = array();
                
                foreach($allowed_keys as $key){
                    if (isset($field_array[$key])){
                        $value = $field_array[$key];
                        if (is_array($value)){
                            if (isset($value['lookup_from'])){
                                $and = new sql_and(new sql_cond('date_deleted', sql::IS, new sql_null()));
                                foreach($value['with_conditions'] as $key => $val){
                                    $and->add(new sql_cond($key, sql::EQUALS, sql::q($val)));
                                }
                                $result = $this->data_source->sql
                                    ->select($value['lookup_from'] . '_id')
                                    ->from($value['lookup_from'])
                                    ->where(
                                        $and
                                    )->execute()
                                    ->results(60 * 60 * 24 * 5); //Cache for 5 days
                                $value = $result[0][$value['lookup_from'] . "_id"];
                            }
                        }
                        $record[$key] = $value;
                    }else{
                        $record[$key] = null;
                    }
                }
                $record['date_created'] = new sql_now();
                //print "<pre>" . print_r(array_values($record), true) . "</pre>";
                $sql->values(array_values($record));
                $schema[] = $record;
            }
            
            //print "<pre>INSERT SQL: {$sql}</pre>\n";
            
            if ($sql->execute()){
                $this->schema = array_merge($this->schema, $schema);
                return true;
            }
            
            return false;
        }
        
        /**
         * Loads the schema for the data source
         */
        public function load_schema(){
            
            $this->schema = $this->sql
                ->select("*")
                ->from('field')
                ->where(
                    new sql_cond('date_deleted', sql::IS, new sql_null())
                )
                ->execute(0)
                ->results();
            
            /*$this->schema = $this->sql
                ->select(new sql('*'))
                ->from('field')
                ->where(new sql_condition(new sql('date_deleted'), 'is', new sql('null')))
                ->execute(0)
                ->results();
            */
            
            $this->data_types = $this->sql
                ->select("*")
                ->from('data_type')
                ->where(
                    new sql_cond('date_deleted', sql::IS, new sql_null())
                )
                ->execute(0)
                ->results();
            
            /*$this->data_types = $this->sql
                ->select(new sql('*'))
                ->from('data_type')
                ->where(new sql_condition(new sql('date_deleted'), 'is', new sql('null')))
                ->execute(0)
                ->results();
            print "<pre>Loading schema</pre>";
            */
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