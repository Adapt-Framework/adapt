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

namespace adapt\interfaces{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    interface data_source_sql extends data_source{
        public function __construct($host = null, $username = null, $password = null, $schema = null, $read_only = false);
        
        /*
         * Properties
         */
        public function pget_schema();
        
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
        public function render_sql(\adapt\sql $sql);
        
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