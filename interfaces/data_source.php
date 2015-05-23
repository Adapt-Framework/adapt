<?php

namespace frameworks\adapt\interfaces{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    interface data_source{
        /*
         * Properties
         */
        public function aget_schema();
        public function aset_schema($schema);
        
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