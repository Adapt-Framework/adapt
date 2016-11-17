<?php

namespace adapt\interfaces{
    
    defined('ADAPT_STARTED') or die;
    
    interface storage_file{
        public function pget_available();
        public function is_key_valid($key);
        public function set($key, $data, $content_type = null, $public = false);
        public function set_by_file($key, $file_path, $content_type = null, $public = false);
        public function get($key, $number_of_bytes = null, $offset = 0);
        public function write_to_file($key, $path = null);
        public function delete($key);
        public function get_size($key);
        public function set_content_type($key, $content_type = null);
        public function get_content_type($key);
        public function set_meta_data($key, $tag, $value);
        public function get_meta_data($key, $tag);
        public function get_meta_data_file($key);
        public function set_meta_data_file($key, $data);
    }
}

?>