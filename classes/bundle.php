<?php

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class bundle extends base{
        
        protected $_data;
        
        protected $_label;
        protected $_name;
        protected $_version;
        protected $_type;
        protected $_namespace;
        protected $_description;
        protected $_copyright;
        protected $_license;
        protected $_depends_on;
        protected $_settings;
        protected $_settings_hash;
        protected $_schema;
        
        protected $_has_changed;
        protected $_is_loaded;
        protected $_is_installed;
        
        public function __construct($name, $data){
            $this->_has_changed = false;
            $this->_is_loaded = false;
            $this->_is_installed = false;
            $this->load($name, $data);
        }
        
        /*
         * Properties
         */
        
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        public function pget_label(){
            return $this->_label;
        }
        
        public function pset_label($value){
            if ($value != $this->_label){
                $this->_label = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_name(){
            return $this->_name;
        }
        
        public function pset_name($value){
            if ($value != $this->_name){
                $this->_name = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_version(){
            return $this->_version;
        }
        
        public function pset_version($value){
            if ($value != $this->_version){
                $this->_version = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_type(){
            return $this->_type;
        }
        
        public function pset_type($value){
            if ($value != $this->_type){
                $this->_type = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_namespace(){
            return $this->_namespace;
        }
        
        public function pset_namespace($value){
            if ($value != $this->_namespace){
                $this->_namespace = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_description(){
            return $this->_description;
        }
        
        public function pset_description($value){
            if ($value != $this->_description){
                $this->_description = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_copyright(){
            return $this->_copyright;
        }
        
        public function pset_copyright($value){
            if ($value != $this->_copyright){
                $this->_copyright = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_license(){
            return $this->_license;
        }
        
        public function pset_license($value){
            if ($value != $this->_license){
                $this->_license = $value;
                $this->_has_changed = true;
            }
        }
        
        public function pget_version_major(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $major;
        }
        
        public function pset_version_major($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $major){
                $this->version = "{$value}.{$minor}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        public function pget_version_minor(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $minor;
        }
        
        public function pset_version_minor($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $minor){
                $this->version = "{$major}.{$value}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        public function pget_version_revision(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $revision;
        }
        
        public function pset_version_revision($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $revision){
                $this->version = "{$major}.{$minor}.{$value}";
                $this->_has_changed = true;
            }
        }
        
        public function pget_depends_on(){
            return $this->_depends_on;
        }
        
        public function pset_depends_on($values){
            $this->_depends_on = $values;
        }
        
        public function pget_is_booted(){
            /*
             * This bundle will be cached on first call, so we need to
             * store is_booted flag somewhere volatile as caching it
             * would cause all sorts of problems.
             */
            
            if (is_null($this->store("adapt.bundle.{$this->name}.booted"))){
                return false;
            }else{
                return $this->store("adapt.bundle.{$this->name}.booted");
            }
        }
        
        public function pset_is_booted($value){
            $this->store("adapt.bundle.{$this->name}.booted", $value);
        }
        
        /*
         * Bundle.xml control
         */
        public function load($bundle_name, $data){
            if ($data instanceof xml){
                $this->_data = $data;
                
                $children = $data->find('bundle')->get(0);
                
                for($i = 0; $i < $children->count(); $i++){
                    $child = $children->get($i);
                    
                    if ($child instanceof xml){
                        switch ($child->tag){
                        case "name":
                            $this->_name = $child->get(0);
                            break;
                        case "label":
                            $this->_label = $child->get(0);
                            break;
                        case "version":
                            $this->_version = $child->get(0);
                            break;
                        case "type":
                            $this->_type = $child->get(0);
                            break;
                        case "namespace":
                            $this->_namespace = $child->get(0);
                            break;
                        case "description":
                            $this->_description = $child->get(0);
                            break;
                        case "copyright":
                            $this->_copyright = $child->get(0);
                            break;
                        case "license":
                            $this->_license = $child->get(0);
                            break;
                        case "depends_on":
                            $this->_depends_on = array();
                            $dependencies = $child->get();
                            
                            foreach($dependencies as $dependency){
                                if ($dependency instanceof xml){
                                    $dependency_child_nodes = $dependency->get();
                                    
                                    $node_name = null;
                                    
                                    foreach($dependency_child_nodes as $node){
                                        if ($node instanceof xml){
                                            
                                            switch ($node->tag){
                                            case "name":
                                                $node_name = $node->get(0);
                                                $this->_depends_on[$node_name] = array();
                                                break;
                                            case "version":
                                                if (!is_null($node_name)){
                                                    $version = $node->get(0);
                                                    $this->_depends_on[$node_name][] = $version;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                                //print $dependency;
                            }
                            
                            break;
                        case "settings":
                            $this->_settings = array();
                            $this->_settins_hash = array();
                            
                            $categories = $child->get();
                            
                            foreach($categories as $category){
                                if ($category instanceof xml && $category->tag == "category"){
                                    $category_name = $category->attr('name');
                                    
                                    $this->_settings[$category_name] = array();
                                    
                                    $setting_pairs = $category->get();
                                    
                                    foreach($setting_pairs as $setting_pair){
                                        if ($setting_pair instanceof xml && $setting_pair->tag == "setting"){
                                            $setting = array();
                                            
                                            $child_nodes = $setting_pair->get();
                                            
                                            foreach($child_nodes as $child_node){
                                                if ($child_node instanceof xml){
                                                    switch($child_node->tag){
                                                    case "name":
                                                    case "label":
                                                    case "default_value":
                                                        $setting[$child_node->tag] = $child_node->get(0);
                                                        break;
                                                    case "default_values":
                                                    case "allowed_values":
                                                        $setting[$child_node->tag] = array();
                                                        $value_nodes = $child_node->get();
                                                        foreach($value_nodes as $value_node){
                                                            if ($value_node instanceof xml && $value_node->tag == "value"){
                                                                $setting[$child_node->tag][] = $value_node->get(0);
                                                            }
                                                        }
                                                        break;
                                                    
                                                    }
                                                    
                                                }
                                            }
                                            
                                            $this->_settings[$category_name][] = $setting;
                                            
                                            $key = $setting['name'];
                                            $value = null;
                                            if (isset($setting['default_value'])){
                                                $value = $setting['default_value'];
                                            }else{
                                                $value = $setting['default_values'];
                                            }
                                            
                                            if ($key){
                                                $this->_settings_hash[$key] = $value;
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        case "schema":
                            $this->_schema = array();
                            
                            $actions = $child->get();
                            
                            foreach($actions as $action){
                                if ($action instanceof xml){
                                    switch($action->tag){
                                    case "add":
                                        /*
                                         * Add to the database
                                         */
                                        $fields_to_add = array();
                                        $records_to_add = array();
                                        
                                        $tables = $action->get();
                                        foreach($tables as $table){
                                            if ($table instanceof xml && $table->tag == 'table'){
                                                $table_name = $table->attr('name');
                                                
                                                $children = $table->get();
                                                foreach($children as $child){
                                                    if ($child instanceof xml){
                                                        switch($child->tag){
                                                        case "field":
                                                            $field_name = $child->attr('name');
                                                            
                                                            if (!isset($fields_to_add[$table_name])){
                                                                $fields_to_add[$table_name] = array();
                                                            }
                                                            
                                                            if (!isset($fields_to_add[$table_name][$field_name])){
                                                                $fields_to_add[$table_name][$field_name] = array();
                                                            }
                                                            
                                                            $attributes = $child->get();
                                                            foreach($attributes as $attr){
                                                                if ($attr instanceof xml){
                                                                    $fields_to_add[$table_name][$field_name][$attr->tag] = $attr->get(0);
                                                                }
                                                            }
                                                            
                                                            if ($child->attr('key') == 'primary'){
                                                                $fields_to_add[$table_name][$field_name]['primary_key'] = "Yes";
                                                            }
                                                            
                                                            if ($child->attr('key') == 'foreign'){
                                                                $fields_to_add[$table_name][$field_name]['referenced_table_name'] = $child->attr('referenced-table-name');
                                                                $fields_to_add[$table_name][$field_name]['referenced_field_name'] = $child->attr('referenced-field-name');
                                                            }
                                                            
                                                            if ($child->attr('auto-increment') == 'Yes'){
                                                                $fields_to_add[$table_name][$field_name]['auto_increment'] = "Yes";
                                                            }
                                                            
                                                            if ($child->attr('index') == 'Yes'){
                                                                $fields_to_add[$table_name][$field_name]['index'] = "Yes";
                                                            }
                                                            
                                                            if ($child->attr('index-size')){
                                                                $fields_to_add[$table_name][$field_name]['index_size'] = $child->attr('index-size');
                                                            }
                                                            
                                                            break;
                                                        case "record":
                                                            $fields = $child->get();
                                                            
                                                            if (!isset($records_to_add[$table_name])){
                                                                $records_to_add[$table_name] = array();
                                                            }
                                                            
                                                            $current_record = array();
                                                            
                                                            foreach($fields as $field){
                                                                if ($field instanceof xml){
                                                                    $field_name = $field->tag;
                                                                    $field_value = $field->get(0);
                                                                    $current_record[$field_name] = $field_value;
                                                                }
                                                            }
                                                            
                                                            $records_to_add[$table_name][] = $current_record;
                                                            break;
                                                        }
                                                    }
                                                }
                                                
                                            }
                                        }
                                        $this->_schema['add'] = array(
                                                'fields' => $fields_to_add,
                                                'records' => $records_to_add
                                            );
                                        );
                                        print new html_pre(print_r($fields_to_add, true));
                                        print new html_pre(print_r($records_to_add, true));
                                        
                                        //if (count($fields_to_add)){
                                        //    foreach($fields_to_add as $table_name => $fields){
                                        //        /* Does the table already exist? */
                                        //        $schema = $this->data_source->get_row_structure($table_name);
                                        //        if (is_array($schema)){
                                        //            /* Alter existing table */
                                        //        }else{
                                        //            /* Create new table */
                                        //            $sql = $this->data_source->sql;
                                        //            
                                        //            $sql->create_table($table_name);
                                        //            
                                        //            foreach($fields as $field_name => $attributes){
                                        //                $data_type = $attributes['data_type'];
                                        //                if ($data_type == 'varchar'){
                                        //                    $data_type .= "({$attributes['max_length']})";
                                        //                }
                                        //                
                                        //                $nullable = true;
                                        //                if (isset($attributes['nullable']) && $attributes['nullable'] == 'No') $nullable = false;
                                        //                
                                        //                $default_value = null;
                                        //                if (isset($attributes['default_value'])) $default_value = $attributes['default_value'];
                                        //                
                                        //                $sql->add($field_name, $data_type, $nullable, $default_value);
                                        //                
                                        //                if (isset($attributes['primary_key']) && $attributes['primary_key'] == 'Yes'){
                                        //                    $auto_increment = true;
                                        //                    
                                        //                    if (isset($attributes['auto_increment']) && $attributes['auto_increment'] == 'No'){
                                        //                        $auto_increment = false;
                                        //                    }
                                        //                    $sql->primary_key($field_name, $auto_increment);
                                        //                }
                                        //                
                                        //                if (isset($attributes['index']) && $attributes['index'] == 'Yes'){
                                        //                    $index_size = null;
                                        //                    
                                        //                    if (isset($attributes['index_size'])){
                                        //                        $index_size = $attributes['index_size'];
                                        //                    }
                                        //                    $sql->index($field_name, $index_size);
                                        //                }
                                        //                
                                        //                if (isset($attributes['referenced_table_name']) && isset($attributes['referenced_field_name'])){
                                        //                    $sql->foreign_key($field_name, $attributes['referenced_table_name'], $attributes['referenced_field_name']);
                                        //                }
                                        //            }
                                        //            
                                        //            $sql->add('date_created', 'datetime');
                                        //            $sql->add('date_modified', 'timestamp');
                                        //            $sql->add('date_deleted', 'datetime');
                                        //            
                                        //            print $sql . "\n\n";
                                        //        }
                                        //    }
                                        //}
                                        //
                                        //if (count($records_to_add)){
                                        //    foreach($records_to_add as $table_name => $records){
                                        //        
                                        //        $sql = $this->data_source->sql;
                                        //        
                                        //        $sql->insert_into($table_name, array_keys($records[0]));
                                        //        
                                        //        foreach($records as $record){
                                        //            $sql->values(array_values($record));
                                        //        }
                                        //        print $sql;
                                        //    }
                                        //    
                                        //    
                                        //}
                                        
                                        //print_r($fields_to_add);
                                        //print_r($records_to_add);
                                        
                                        break;
                                    case "remove":
                                        /*
                                         * Remove from the database
                                         */
                                        
                                        break;
                                    }
                                }
                            }
                            break;
                        default:
                            /* Do we have a handler to handle the tag? */
                        }
                        
                    }
                }
                
                $this->_is_loaded = true;
            }

        }
        
        public function save(){
            
        }
        
        public function register_config_handler($tag_name, $function){
            
        }
        
        public function apply_settings(){
            /*
             * We need to first apply the settings and
             * then we need to re-apply the global settings
             * so they take priority.
             */
            if (is_array($this->_settings_hash)){
                /* Get a hash of the settings currently in affect */
                $current_settings = $this->get_settings();
                
                /* Merge with our settings */
                $current_settings = array_merge($current_settings, $this->_settings_hash);
                
                /* Get the global settings */
                $global_settings = $this->bundles->get_global_settings();
                
                /* Merge with the global settings */
                $current_settings = array_merge($current_settings, $global_settings);
                
                /* Override the current settings with our updated copy */
                $this->set_settings($current_settings);
            }
            
        }
        
        public function boot(){
            //print "<pre>Booting (bundle level): {$this->name}</pre>";
            if (!$this->is_booted){
                
                $this->apply_settings();
                
                if ($this->type == 'application'){
                    //print "<pre>Booting application</pre>";
                    $dependency_list = $this->bundles->get_dependency_list($this->name, $this->version);
                    
                    if (is_array($dependency_list)){
                        
                        $dependency_list = array_reverse($dependency_list);
                        //print new html_pre(print_r($dependency_list, true));
                        foreach($dependency_list as $bundle_data){
                            //print "<pre>Loading {$bundle_data['name']} {$bundle_data['version']}</pre>";
                            //print "<pre>" . print_r($this->store('adapt.namespaces'), true) . "</pre>";
                            $bundle = $this->bundles->load_bundle($bundle_data['name'], $bundle_data['version']);
                            if ($bundle instanceof bundle && $bundle->is_loaded){
                                //print "<pre>Loaded {$bundle->name} {$bundle->version} (" . get_class($bundle) . ")</pre>";
                                if (!$bundle->boot()){
                                    $this->error("Unable to boot '{$bundle_data['name']}'");
                                    return false;
                                }
                            }else{
                                $this->error("Unable to boot '{$bundle_data['name']}', the boot process failed.");
                                return false;
                            }
                        }
                    
                        $this->booted = true;
                    }else{
                        $errors = $this->bundles->errors(true);
                        foreach($errors as $error) $this->error($error);
                        
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        public function install(){
            
        }
        
        public function is_installed(){
            /*
             * self::_is_installed property is only used by
             * this function 
             */
            
            if ($this->_is_installed){
                
            }
            
            return true;
        }
        
    }
    
    
}

?>