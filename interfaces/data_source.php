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
    
    interface data_source{
        /*
         * Properties
         */
        public function pget_schema();
        public function pset_schema($schema);
        
        /*
         * Get the number of datasets in this source
         */
        public function get_number_of_datasets();
        
        /*
         * Get a list of datasets
         */
        public function get_dataset_list();
        
        /*
         * Retrieve record count
         */
        public function get_number_of_rows($dataset_index);
        
        /*
         * Retrieve record structure
         */
        public function get_row_structure($dataset_index);
        
        /*
         * Retrieve data types
         */
        public function get_data_type($data_type);
        public function get_data_type_id($data_type);
        public function get_base_data_type($data_type);
        
        /*
         * Retrieve record
         */
        public function get($dataset_index, $row_offset, $number_of_rows = 1);
    }
    
    
}

?>