<?php

namespace frameworks\adapt\interfaces{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    interface data_source_sql extends data_source{
        public function __construct($host = null, $username = null, $password = null, $schema = null, $read_only = false);
        
        /*
         * Properties
         */
        public function aget_schema();
        
        /*
         * SQL Execution
         */
        public function write($sql);
        public function read($sql);
        public function query($sql, $write = false);
        public function fetch($statement_handle, $fetch_type = self::FETCH_ASSOC);
        public function last_insert_id();
        
        /*
         * Host management
         */
        public function add_host($host, $username, $password, $schema, $read_only = false);
        public function connect($host);
        public function disconnect($host);
        public function get_host($writable = false);
        
        /*
         * SQL Rendering
         */
        public function render_sql(\frameworks\adapt\sql $sql);
        
        /*
         * Escaping values
         */
        public function escape($string);
        
        /*
         * Data validation
         */
        public function validate($table_name, $field_name, $value);
        
        /*
         * Data presentation
         */
        public function format($table_name, $field_name, $value);
        public function unformat($table_name, $field_name, $value);
        
        /*
         * Data types
         */
        public function convert_data_type($type, $signed = true, $zero_fill = false);
    }
    
    
}

?>