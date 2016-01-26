<?php

namespace adapt{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class bundle_adapt extends bundle {
        
        public function __construct($bundle_data){
            parent::__construct('adapt', $bundle_data);
        }
        
        public function boot(){
            if (parent::boot()){
                
                /* Extend the root controller and add adapts controller  */
                \application\controller_root::extend('view__adapt', function($_this){
                    return $_this->load_controller("\\adapt\\controller_adapt");
                });
                
                /* Define the DOM */
                $this->dom = new page();
                
                /* Yay! */
                return true;
            }
            
            return false;
        }
    }
    
    
    //class bundle_adapt extends bundle{
    //    
    //    public function __construct($data){
    //        parent::__construct('adapt', $data);
    //        
    //        $this->add_boot_config_handler(
    //            'settings',
    //            function($_this, $xml_node){
    //                $settings = array();
    //                    
    //                if ($xml_node instanceof xml && $xml_node->tag == 'settings'){
    //                    $categories = $xml_node->get();
    //                    
    //                    foreach($categories as $category){
    //                        if ($category instanceof xml && $category->tag == 'category'){
    //                            $cat_settings = $category->get();
    //                            
    //                            foreach($cat_settings as $setting){
    //                                $setting_name = "";
    //                                $setting_value = "";
    //                                
    //                                if ($setting instanceof xml && $setting->tag == 'setting'){
    //                                    
    //                                    
    //                                    $children = $setting->get();
    //                                    
    //                                    foreach($children as $child){
    //                                        if ($child instanceof xml){
    //                                            switch($child->tag){
    //                                            case "name":
    //                                                $setting_name = trim($child->get(0));
    //                                                break;
    //                                            case "default_value":
    //                                                $setting_value = trim($child->get(0));
    //                                                break;
    //                                            case "default_values":
    //                                                $setting_value = array();
    //                                                
    //                                                $values = $child->get();
    //                                                foreach($values as $value){
    //                                                    if ($value instanceof xml && $value->tag == 'value'){
    //                                                        $setting_value[] = $value->get(0);
    //                                                    }
    //                                                }
    //                                                break;
    //                                            }
    //                                        }
    //                                    }
    //                                    
    //                                    if ($setting_name != ""){
    //                                        $_this->setting($setting_name, $setting_value);
    //                                        $settings[$setting_name] = $setting_value;
    //                                    }
    //                                }
    //                            }
    //                        }
    //                    }
    //                }
    //            }
    //        );
    //        
    //        $this->add_install_config_handler(
    //            'schema',
    //            function($_this, $xml_node){
    //                if ($xml_node instanceof xml && $xml_node->tag == "schema"){
    //                    /*
    //                     * This handler is used for schema building.
    //                     * If this bundle is the owner of the table as defined in
    //                     * the fields table then the table is altered to match exatctly
    //                     * match the definintion.
    //                     *
    //                     * If this bundle is not the owner then the table is modified
    //                     * as defined by this bundle.
    //                     */
    //                    
    //                    if ($xml_node instanceof xml && $xml_node->tag == 'schema'){
    //                        $actions = $xml_node->get();
    //                        
    //                        foreach($actions as $action){
    //                            if ($action instanceof xml){
    //                                switch($action->tag){
    //                                case "add":
    //                                    /*
    //                                     * Add to the database
    //                                     */
    //                                    $fields_to_add = array();
    //                                    $records_to_add = array();
    //                                    
    //                                    $tables = $action->get();
    //                                    foreach($tables as $table){
    //                                        if ($table instanceof xml && $table->tag == 'table'){
    //                                            $table_name = $table->attr('name');
    //                                            
    //                                            $children = $table->get();
    //                                            foreach($children as $child){
    //                                                if ($child instanceof xml){
    //                                                    switch($child->tag){
    //                                                    case "field":
    //                                                        $field_name = $child->attr('name');
    //                                                        
    //                                                        if (!isset($fields_to_add[$table_name])){
    //                                                            $fields_to_add[$table_name] = array();
    //                                                        }
    //                                                        
    //                                                        if (!isset($fields_to_add[$table_name][$field_name])){
    //                                                            $fields_to_add[$table_name][$field_name] = array();
    //                                                        }
    //                                                        
    //                                                        $attributes = $child->get();
    //                                                        foreach($attributes as $attr){
    //                                                            if ($attr instanceof xml){
    //                                                                $fields_to_add[$table_name][$field_name][$attr->tag] = $attr->get(0);
    //                                                            }
    //                                                        }
    //                                                        
    //                                                        if ($child->attr('key') == 'primary'){
    //                                                            $fields_to_add[$table_name][$field_name]['primary_key'] = "Yes";
    //                                                        }
    //                                                        
    //                                                        if ($child->attr('key') == 'foreign'){
    //                                                            $fields_to_add[$table_name][$field_name]['referenced_table_name'] = $child->attr('referenced-table-name');
    //                                                            $fields_to_add[$table_name][$field_name]['referenced_field_name'] = $child->attr('referenced-field-name');
    //                                                        }
    //                                                        
    //                                                        if ($child->attr('auto-increment') == 'Yes'){
    //                                                            $fields_to_add[$table_name][$field_name]['auto_increment'] = "Yes";
    //                                                        }
    //                                                        
    //                                                        if ($child->attr('index') == 'Yes'){
    //                                                            $fields_to_add[$table_name][$field_name]['index'] = "Yes";
    //                                                        }
    //                                                        
    //                                                        if ($child->attr('index-size')){
    //                                                            $fields_to_add[$table_name][$field_name]['index_size'] = $child->attr('index-size');
    //                                                        }
    //                                                        
    //                                                        break;
    //                                                    case "record":
    //                                                        $fields = $child->get();
    //                                                        
    //                                                        if (!isset($records_to_add[$table_name])){
    //                                                            $records_to_add[$table_name] = array();
    //                                                        }
    //                                                        
    //                                                        $current_record = array();
    //                                                        
    //                                                        foreach($fields as $field){
    //                                                            if ($field instanceof xml){
    //                                                                $field_name = $field->tag;
    //                                                                $field_value = $field->get(0);
    //                                                                $current_record[$field_name] = $field_value;
    //                                                            }
    //                                                        }
    //                                                        
    //                                                        $records_to_add[$table_name][] = $current_record;
    //                                                        break;
    //                                                    }
    //                                                }
    //                                            }
    //                                            
    //                                        }
    //                                    }
    //                                    
    //                                    if (count($fields_to_add)){
    //                                        foreach($fields_to_add as $table_name => $fields){
    //                                            /* Does the table already exist? */
    //                                            $schema = $this->data_source->get_row_structure($table_name);
    //                                            if (is_array($schema)){
    //                                                /* Alter existing table */
    //                                            }else{
    //                                                /* Create new table */
    //                                                $sql = $this->data_source->sql;
    //                                                
    //                                                $sql->create_table($table_name);
    //                                                
    //                                                foreach($fields as $field_name => $attributes){
    //                                                    $data_type = $attributes['data_type'];
    //                                                    if ($data_type == 'varchar'){
    //                                                        $data_type .= "({$attributes['max_length']})";
    //                                                    }
    //                                                    
    //                                                    $nullable = true;
    //                                                    if (isset($attributes['nullable']) && $attributes['nullable'] == 'No') $nullable = false;
    //                                                    
    //                                                    $default_value = null;
    //                                                    if (isset($attributes['default_value'])) $default_value = $attributes['default_value'];
    //                                                    
    //                                                    $sql->add($field_name, $data_type, $nullable, $default_value);
    //                                                    
    //                                                    if (isset($attributes['primary_key']) && $attributes['primary_key'] == 'Yes'){
    //                                                        $auto_increment = true;
    //                                                        
    //                                                        if (isset($attributes['auto_increment']) && $attributes['auto_increment'] == 'No'){
    //                                                            $auto_increment = false;
    //                                                        }
    //                                                        $sql->primary_key($field_name, $auto_increment);
    //                                                    }
    //                                                    
    //                                                    if (isset($attributes['index']) && $attributes['index'] == 'Yes'){
    //                                                        $index_size = null;
    //                                                        
    //                                                        if (isset($attributes['index_size'])){
    //                                                            $index_size = $attributes['index_size'];
    //                                                        }
    //                                                        $sql->index($field_name, $index_size);
    //                                                    }
    //                                                    
    //                                                    if (isset($attributes['referenced_table_name']) && isset($attributes['referenced_field_name'])){
    //                                                        $sql->foreign_key($field_name, $attributes['referenced_table_name'], $attributes['referenced_field_name']);
    //                                                    }
    //                                                }
    //                                                
    //                                                $sql->add('date_created', 'datetime');
    //                                                $sql->add('date_modified', 'timestamp');
    //                                                $sql->add('date_deleted', 'datetime');
    //                                                
    //                                                print $sql . "\n\n";
    //                                            }
    //                                        }
    //                                    }
    //                                    
    //                                    if (count($records_to_add)){
    //                                        foreach($records_to_add as $table_name => $records){
    //                                            
    //                                            $sql = $this->data_source->sql;
    //                                            
    //                                            $sql->insert_into($table_name, array_keys($records[0]));
    //                                            
    //                                            foreach($records as $record){
    //                                                $sql->values(array_values($record));
    //                                            }
    //                                            print $sql;
    //                                        }
    //                                        
    //                                        
    //                                    }
    //                                    
    //                                    //print_r($fields_to_add);
    //                                    //print_r($records_to_add);
    //                                    
    //                                    break;
    //                                case "remove":
    //                                    /*
    //                                     * Remove from the database
    //                                     */
    //                                    
    //                                    break;
    //                                }
    //                            }
    //                        }
    //                    }
    //                }
    //            }
    //        );
    //    }
    //    
    //    public function boot($boot_dependencies = true){
    //        if (!$this->has_booted){
    //            parent::boot($boot_dependencies);
    //            $this->dom = new page();
    //        }
    //    }
    //    
    //    public function generate_xml_schema($bundle_name, $table_name){
    //        $xml = xml_table(array('name' => $table_name));
    //        
    //        return $xml;
    //    }
    //    
    //}
    
    
}

?>