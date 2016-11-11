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
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * Controls access to the bundles bundle.xml, bundle installation
     * and bundle booting.
     *
     * @property-read boolean $is_loaded
     * Has the bundle.xml be parsed and loaded?
     * @property string $label
     * The label of this bundle.
     * @property string $name
     * The name of this bundle.
     * @property string $version
     * The version of this bundle.
     * @property string $type
     * The type of bundle.
     * @property string $namespace
     * The namespace of this bundle.
     * @property string $description
     * The description of this bundle.
     * @property string $copyright
     * The copyright of this bundle.
     * @property string $license
     * The license for this bundle.
     * @property integer $version_major
     * The major version of this bundle.
     * @property integer $version_minor
     * The minor version of this bundle.
     * @property integer $version_revision
     * The version revision of this bundle.
     * @property array $depends_on
     * A list of dependencies this bundle depends on.
     * @property boolean $is_booted
     * Has this bundle been booted?
     */
    class bundle extends base{

        /** @ignore */
        protected $_file_store;
        /** @ignore */
        protected $_data;
        /** @ignore */
        protected $_label;
        /** @ignore */
        protected $_name;
        /** @ignore */
        protected $_version;
        /** @ignore */
        protected $_type;
        /** @ignore */
        protected $_namespace;
        /** @ignore */
        protected $_description;
        /** @ignore */
        protected $_copyright;
        /** @ignore */
        protected $_license;
        /** @ignore */
        protected $_depends_on;
        /** @ignore */
        protected $_settings;
        /** @ignore */
        protected $_settings_hash;
        /** @ignore */
        protected $_schema;
        /** @ignore */
        protected $_has_changed;
        /** @ignore */
        protected $_is_loaded;
        /** @ignore */
        protected $_is_installed;
        /** @ignore */
        protected $_local_config_handlers;
        /** @ignore */
        protected $_local_install_handlers;
        /** @ignore */
        protected $_config_handlers_to_process;
        /**
         * Constructor
         *
         * @access public
         * @param string $name
         * Name of the bundle to load
         * @param xml $data
         * The bundle.xml structure
         */
        public function __construct($name, $data){
            $this->_local_config_handlers = array();
            $this->_local_install_handlers = array();
            $this->_config_handlers_to_process = array();
            $this->_has_changed = false;
            $this->_is_loaded = false;
            $this->load($name, $data);
        }
        
        /*
         * Properties
         */
        /** @ignore */
        public function pget_file_store(){
            if ($this->_file_store) {
                return $this->_file_store;
            }

            $store = parent::pget_file_store();

            if ($store instanceof storage_file_system) {
                return $store;
            } else {
                $this->_file_store = new storage_file_system();
                return $this->_file_store;
            }
        }

        /** @ignore */
        public function pget_is_loaded(){
            return $this->_is_loaded;
        }
        
        /** @ignore */
        public function pget_label(){
            return $this->_label;
        }
        
        /** @ignore */
        public function pset_label($value){
            if ($value != $this->_label){
                $this->_label = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_name(){
            return $this->_name;
        }
        
        /** @ignore */
        public function pset_name($value){
            if ($value != $this->_name){
                $this->_name = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_version(){
            return $this->_version;
        }
        
        /** @ignore */
        public function pset_version($value){
            if ($value != $this->_version){
                $this->_version = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_type(){
            return $this->_type;
        }
        
        /** @ignore */
        public function pset_type($value){
            if ($value != $this->_type){
                $this->_type = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_namespace(){
            return $this->_namespace;
        }
        
        /** @ignore */
        public function pset_namespace($value){
            if ($value != $this->_namespace){
                $this->_namespace = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_description(){
            return $this->_description;
        }
        
        /** @ignore */
        public function pset_description($value){
            if ($value != $this->_description){
                $this->_description = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_copyright(){
            return $this->_copyright;
        }
        
        /** @ignore */
        public function pset_copyright($value){
            if ($value != $this->_copyright){
                $this->_copyright = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_license(){
            return $this->_license;
        }
        
        /** @ignore */
        public function pset_license($value){
            if ($value != $this->_license){
                $this->_license = $value;
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_version_major(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $major;
        }
        
        /** @ignore */
        public function pset_version_major($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $major){
                $this->version = "{$value}.{$minor}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_version_minor(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $minor;
        }
        
        /** @ignore */
        public function pset_version_minor($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $minor){
                $this->version = "{$major}.{$value}.{$revision}";
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_version_revision(){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            
            return $revision;
        }
        
        /** @ignore */
        public function pset_version_revision($value){
            $version = $this->version;
            list($major, $minor, $revision) = explode(".", $version);
            if ($value != $revision){
                $this->version = "{$major}.{$minor}.{$value}";
                $this->_has_changed = true;
            }
        }
        
        /** @ignore */
        public function pget_depends_on(){
            return $this->_depends_on;
        }
        
        /** @ignore */
        public function pset_depends_on($values){
            $this->_depends_on = $values;
        }
        
        /** @ignore */
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
        
        /** @ignore */
        public function pset_is_booted($value){
            $this->store("adapt.bundle.{$this->name}.booted", $value);
        }
        
        /*
         * Serialization
         */
        /** @ignore */
        public function __wakeup(){
            $handlers = $this->_local_config_handlers;
            $this->_local_config_handlers = array();
            
            foreach($handlers as $tag => $handler){
                $this->register_config_handler($handler['bundle_name'], $tag, $handler['function']);
            }
            
            $handlers = $this->_local_install_handlers;
            $this->_local_install_handlers = array();
            
            foreach($handlers as $target => $handler_array){
                foreach($handler_array as $handler){
                    $this->register_install_handler($handler['bundle_name'], $target, $handler['function']);
                }
            }
            
        }
        
        /**
         * Returns the settings for this bundle as defined
         * in the bundles bundle.xml file.
         *
         * @access public
         * @return array
         * Returns a hash array of the bundles settings.
         */
        public function get_bundle_settings(){
            return $this->_settings;
        }
        
        /**
         * Loads a bunble
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle to load
         * @param xml $data
         * The contents of the bundles bundle.xml file.
         */
        public function load($bundle_name, $data){
            //print "<pre>Loading: {$bundle_name}</pre>";
            if ($data instanceof xml){
                $this->_data = $data;
                
                $children = $data->find('bundle')->get(0);
                
                for($i = 0; $i < $children->count(); $i++){
                    $child = $children->get($i);
                    //print "<pre>{$child->tag}</pre>";
                    
                    if ($child instanceof xml){
                        
                        switch (strtolower($child->tag)){
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
                                            
                                            switch (strtolower($node->tag)){
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
                                if ($category instanceof xml && strtolower($category->tag) == "category"){
                                    $category_name = $category->attr('name');
                                    
                                    $this->_settings[$category_name] = array();
                                    
                                    $setting_pairs = $category->get();
                                    
                                    foreach($setting_pairs as $setting_pair){
                                        if ($setting_pair instanceof xml && strtolower($setting_pair->tag) == "setting"){
                                            $setting = array();
                                            
                                            $child_nodes = $setting_pair->get();
                                            
                                            foreach($child_nodes as $child_node){
                                                if ($child_node instanceof xml){
                                                    switch(strtolower($child_node->tag)){
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
                                                            if ($value_node instanceof xml && strtolower($value_node->tag) == "value"){
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
                                    switch(strtolower($action->tag)){
                                    case "add":
                                        /*
                                         * Add to the database
                                         */
                                        $fields_to_add = array();
                                        $records_to_add = array();
                                        
                                        $tables = $action->get();
                                        foreach($tables as $table){
                                            if ($table instanceof xml && strtolower($table->tag) == 'table'){
                                                $table_name = $table->attr('name');
                                                
                                                $table_children = $table->get();
                                                foreach($table_children as $child){
                                                    if ($child instanceof xml){
                                                        switch(strtolower($child->tag)){
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
                                                                    /*switch($attr->tag){
                                                                    case "allowed_values":
                                                                        $value_nodes = $attr->get();
                                                                        $values = array();
                                                                        foreach($value_nodes as $value_node){
                                                                            if ($value_node instanceof xml && $value_node->tag == 'value'){
                                                                                $values[] = $value_node->get(0);
                                                                            }
                                                                        }
                                                                        $fields_to_add[$table_name][$field_name][$attr->tag] = $values;
                                                                        break;
                                                                    default:*/
                                                                        $fields_to_add[$table_name][$field_name][$attr->tag] = $attr->get(0);
                                                                    /*}*/
                                                                }
                                                            }
                                                            
                                                            if (strtolower($child->attr('key')) == 'primary'){
                                                                $fields_to_add[$table_name][$field_name]['primary_key'] = "Yes";
                                                            }else{
                                                                $fields_to_add[$table_name][$field_name]['primary_key'] = "No";
                                                            }
                                                            
                                                            if (strtolower($child->attr('key')) == 'foreign'){
                                                                $fields_to_add[$table_name][$field_name]['referenced_table_name'] = $child->attr('referenced-table-name');
                                                                $fields_to_add[$table_name][$field_name]['referenced_field_name'] = $child->attr('referenced-field-name');
                                                            }
                                                            
                                                            if (strtolower($child->attr('auto-increment')) == 'yes'){
                                                                $fields_to_add[$table_name][$field_name]['auto_increment'] = "Yes";
                                                            }else{
                                                                $fields_to_add[$table_name][$field_name]['auto_increment'] = "No";
                                                            }
                                                            
                                                            if (strtolower($child->attr('index')) == 'yes'){
                                                                $fields_to_add[$table_name][$field_name]['index'] = "Yes";
                                                            }else{
                                                                $fields_to_add[$table_name][$field_name]['index'] = "No";
                                                            }
                                                            
                                                            if ($child->attr('index-size')){
                                                                $fields_to_add[$table_name][$field_name]['index_size'] = $child->attr('index-size');
                                                            }
                                                            
                                                            $keys = array(
                                                                'label' => 'label',
                                                                'placeholder_label' => 'placeholder-label',
                                                                'description' => 'description',
                                                                'data_type' => 'data-type',
                                                                'signed' => 'signed',
                                                                'nullable' => 'nullable',
                                                                'timestamp' => 'timestamp',
                                                                'max_length' => 'max-length',
                                                                'default_value' => 'default-value',
                                                                'allowed_values' => 'allowed-values',
                                                                'lookup_table' => 'lookup-table'
                                                            );
                                                            
                                                            $yes_no_fields = array(
                                                                'signed' => 'No', 'nullable' => 'Yes', 'timestamp' => 'No'
                                                            );
                                                            
                                                            foreach($keys as $key => $value){
                                                                if (!isset($fields_to_add[$table_name][$field_name][$key])){
                                                                    // TODO: this assignment needs an ucwords() wrapper
                                                                    $fields_to_add[$table_name][$field_name][$key] = $child->attr($value);
                                                                    
                                                                    if (in_array($key, array_keys($yes_no_fields))){
                                                                        if (is_null($fields_to_add[$table_name][$field_name][$key]) || $fields_to_add[$table_name][$field_name][$key] == ""){
                                                                            $fields_to_add[$table_name][$field_name][$key] = $yes_no_fields[$key];
                                                                        }
                                                                    }
                                                                }
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
                                                                    
                                                                    if ($field->attr('get-from')/* && $field->attr('where-name-is')*/){
                                                                        $conditions = [];
                                                                        $attrs = $field->attributes;
                                                                        foreach($attrs as $key => $value){
                                                                            $match = [];
                                                                            if (preg_match("/^where\-([-a-zA-Z0-9]+)\-is$/", $key, $match)){
                                                                                $conditions[str_replace("-", "_", $match[1])] = $value;
                                                                            }
                                                                        }
                                                                        
                                                                        $current_record[$field_name] = [
                                                                            'lookup_from' => $field->attr('get-from'),
                                                                            'with_conditions' => $conditions
                                                                        ];
                                                                        //print_r($current_record);
                                                                        //if ($field_name == "organisation_id" && $table_name == "host"){
                                                                        //    print "TABLE: {$table_name}\n";
                                                                        //    //die;
                                                                        //}
                                                                        //$current_record[$field_name] = array('_lookup_table' => $field->attr('get-from'), '_lookup_name' => $field->attr('where-name-is'));
                                                                    }else{
                                                                        $field_value = $field->get(0);
                                                                        $current_record[$field_name] = $field_value;
                                                                    }
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
                                        //print new html_pre(print_r($fields_to_add, true));
                                        //print new html_pre(print_r($records_to_add, true));
                                        
                                        //
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
//<<<<<<< HEAD
//                            $handlers = $this->store("adapt.config_handlers");
//                            //print "<pre>CONFIG HANDLERS: " . print_r($handlers, true) . "</pre>";
//                            if (is_array($handlers) && isset($handlers[$child->tag])){
//                                
//                                $handler = $handlers[$child->tag];
//                                
//                                $bundle = $this->bundles->load_bundle($handler['bundle_name']);
//                                if ($bundle instanceof bundle && $bundle->name == $handler['bundle_name']){
//                                    $function = $handler['function'];
//                                    
//                                    if (method_exists($bundle, $function)){
//                                        $bundle->$function($this, $child);
//                                    }
//                                    
//                                }
//=======
                            
                            /**
                             * The boot and install process require bundles to
                             * be processed in a particular order, loading does
                             * not, and so some config handlers may not
                             * have been defined at this point.
                             *
                             * This is a great BIG BUG!
                             */
                            
                            //$handlers = $this->store("adapt.config_handlers") ?: [];
                            
                            if (!is_array($this->_config_handlers_to_process)) $this->_config_handlers_to_process = [];
                            
                            if (!is_array($this->_config_handlers_to_process[$child->tag])){
                                $this->_config_handlers_to_process[$child->tag] = [];
//>>>>>>> horizon
                            }
                            
                            $this->_config_handlers_to_process[$child->tag][] = $child;
                            
                            //print "<pre>CONFIG HANDLERS: " . print_r($handlers, true) . "</pre>";
                            //if (is_array($handlers) && isset($handlers[$child->tag])){
                            //    if (!is_array($handlers[$child->tag]['actions'])){
                            //        $handlers[$child->tag]['actions'] = [];
                            //    }
                            //    
                            //    $handlers[$child->tag]['actions'][] = $child;
                            //}else{
                            //    $handlers[$child->tag] = [];
                            //    $handlers[$child->tag]['actions'] = [$child];
                            //    
                            //}
                            //$this->store('adapt.config_handlers', $handlers);
                            
                            
                            break;
                        }
                        
                        
                        
                    }
                }
                
                $this->_is_loaded = true;
            }

        }
        
        /**
         * Saves the bundles bundle.xml
         *
         * @ignore
         * @todo Write this function
         */
        public function save(){
            
        }
        
        /**
         * Registers a config handler.
         * This allows bundles to extend bundle.xml and add there own tags. Upon parsing the
         * bundle.xml file any tags defined by this function will be parsed to the function
         * named $function_name for processing.
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle defining the tag.
         * @param string $tag_name
         * The XML tag name to be processed
         * @param string $function_name
         * The name of the function to process the tag.
         */
        public function register_config_handler($bundle_name, $tag_name, $function_name){
            
            $handler = array(
                'bundle_name' => $bundle_name,
                'function' => $function_name
            );
            
            $this->_local_config_handlers[$tag_name] = $handler;
            
            $handlers = $this->store("adapt.config_handlers");
            if (is_array($handlers)){
                $handlers[$tag_name] = $handler;
            }else{
                $handlers = array($tag_name => $handler);
            }
            //print "<pre>register_config_handler: " . print_r($handlers, true) . "</pre>";
            $this->store("adapt.config_handlers", $handlers);
        }
        
        /**
         * Registers an install handler.
         * This allows bundles to extend bundle.xml and add there own tags. Upon parsing the
         * bundle.xml file any tags defined by this function will be parsed to the function
         * named $function_name for processing.
         *
         * @access public
         * @param string $bundle_name
         * The name of the bundle processing the tag
         * @param string $target_bundle
         * The bundle that defined the tag
         * @param string $function_name
         * The name of the function to process the tag.
         */
        public function register_install_handler($bundle_name, $target_bundle, $function_name){
            
            $handler = array(
                'bundle_name' => $bundle_name,
                'function' => $function_name
            );
            
            if (!is_array($this->_local_install_handlers[$target_bundle])){
                $this->_local_install_handlers[$target_bundle] = array();
            }
            $this->_local_install_handlers[$target_bundle][] = $handler;
            
            $handlers = $this->store("adapt.install_handlers");
            if (is_array($handlers)){
                if (is_array($handlers[$target_bundle])){
                    $handlers[$target_bundle][] = $handler;
                }else{
                    $handlers[$target_bundle] = array($handler);
                }
                //$handlers[$target_bundle] = $handler;
            }else{
                $handlers = array($target_bundle => array($handler));
            }
            //print "<pre>register_config_handler: " . print_r($handlers, true) . "</pre>";
            $this->store("adapt.install_handlers", $handlers);
        }
        
        /**
         * Applies the bundles settings to the system.
         */
        public function apply_settings(){
            /*
             * We need to first apply the settings and
             * then we need to re-apply the global settings
             * so they take priority.
             *
             * @access public
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
        
        /**
         * Boots the bundle
         *
         * @access public
         * @return boolean
         * Returns true if the bundle booted successfully, otherwise
         * returns false.
         */
        public function boot(){
            if (!$this->is_booted){
                $this->apply_settings();
                
                if ($this->type == 'application'){
                    $dependency_list = $this->bundles->get_dependency_list($this->name, $this->version);
                    
                    if (is_array($dependency_list)){
                        
                        $dependency_list = array_reverse($dependency_list);
                        
                        foreach($dependency_list as $bundle_data){
                            $bundle = $this->bundles->load_bundle($bundle_data['name'], $bundle_data['version']);
                            if ($bundle instanceof bundle && $bundle->is_loaded){
                                
                                if (!$bundle->boot()){
                                    $this->error("Unable to boot '{$bundle_data['name']}'");
                                    $this->error($bundle->errors(true));
                                    
                                    return false;
                                }
                            }else{
                                $this->error("Unable to boot '{$bundle_data['name']}', the boot process failed.");
                                $this->error($this->bundles->errors(true));
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
            
            if (!$this->is_installed()){
                if (!$this->install()){
                    $this->error("Failed to install {$this->name}");
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Installs the bundle.
         *
         * @access public
         */
        public function install(){
            if (!$this->is_installed() && !$this->is_installing()){
                
                /* Mark as installing */
                $this->file_store->set("adapt/installation/{$this->name}-{$this->version}", "true", "text/plain");
                
                if (is_array($this->_schema) && $this->data_source instanceof data_source_sql){
                    /*
                     * We have a schema
                     */
                    if (is_array($this->_schema['add'])){
                        
                        /*
                         * Lets add to the schema
                         */
                        
                        if (is_array($this->_schema['add']['fields'])){
                            /*
                             * Adding tables
                             */
                            if (count($this->_schema['add']['fields'])){
                                
                                foreach($this->_schema['add']['fields'] as $table_name => $fields){
                                    /* Does the table already exist? */
                                    $schema = $this->data_source->get_row_structure($table_name);
                                    if (is_array($schema)){
                                        /* Alter existing table */
                                        $field_registrations = array();
                                        
                                        $sql = $this->data_source->sql;
                                        
                                        $sql->alter_table($table_name);
                                        
                                        $last_field = null;
                                        foreach($schema as $f){
                                            if ($f['field_name'] == 'date_created') break;
                                            $last_field = $f['field_name'];
                                        }
                                        
                                        foreach($fields as $field_name => $attributes){
                                            $data_type = $attributes['data_type'];
                                            if ($data_type == 'varchar'){
                                                $data_type .= "({$attributes['max_length']})";
                                            }elseif(substr($data_type, 0, 4) == "enum"){
                                                $values = explode("(", $data_type);
                                                $values = explode(")", $values[1]);
                                                $values = $values[0];
                                                
                                                $values = explode(",", $values);
                                                
                                                for($i = 0; $i < count($values); $i++){
                                                    $values[$i] = preg_replace("/'|\"/", "", $values[$i]);
                                                    $values[$i] = sql::q(trim($values[$i]));
                                                    //$values[$i] = sql::q(trim(trim($values[$i], "'"), "\""));
                                                }
                                                
                                                $values = implode(", ", $values);
                                                $attributes['allowed_values'] = "[" . $values . "]";
                                                $attributes['data_type'] = "enum";
                                            }
                                            
                                            $nullable = true;
                                            if (isset($attributes['nullable']) && $attributes['nullable'] == 'No') $nullable = false;
                                            
                                            $default_value = null;
                                            if (isset($attributes['default_value'])) $default_value = $attributes['default_value'];
                                            
                                            $sql->add($field_name, $data_type, $nullable, $default_value, false, false, $last_field);
                                            $last_field = $field_name;
                                            
                                            if (isset($attributes['primary_key']) && $attributes['primary_key'] == 'Yes'){
                                                $auto_increment = true;
                                                
                                                if (isset($attributes['auto_increment']) && $attributes['auto_increment'] == 'No'){
                                                    $auto_increment = false;
                                                }
                                                $sql->primary_key($field_name, $auto_increment);
                                            }
                                            
                                            if (isset($attributes['index']) && $attributes['index'] == 'Yes'){
                                                $index_size = null;
                                                
                                                if (isset($attributes['index_size'])){
                                                    $index_size = $attributes['index_size'];
                                                }
                                                $sql->index($field_name, $index_size);
                                            }
                                            
                                            if (isset($attributes['referenced_table_name']) && isset($attributes['referenced_field_name'])){
                                                $sql->foreign_key($field_name, $attributes['referenced_table_name'], $attributes['referenced_field_name']);
                                            }
                                            
                                            $field_registration = array(
                                                'bundle_name' => $this->name,
                                                'table_name' => $table_name,
                                                'field_name' => $field_name,
                                                'referenced_table_name' => $attributes['referenced_table_name'],
                                                'referenced_field_name' => $attributes['referenced_field_name'],
                                                'label' => $attributes['label'],
                                                'placeholder_label' => $attributes['placeholder_label'],
                                                'description' => $attributes['description'],
                                                'data_type_id' => array('_lookup_table' => 'data_type', '_lookup_name' => $attributes['data_type']),
                                                'primary_key' => $attributes['primary_key'] == "Yes" ? "Yes" : "No",
                                                'signed' => $attributes['signed'] == "Yes" ? "Yes" : "No",
                                                'nullable' => $attributes['nullable'] == "No" ? "No" : "Yes",
                                                'auto_increment' => $attributes['auto_increment'] == "Yes" ? "Yes" : "No",
                                                'timestamp' => $attributes['timestamp'] == "Yes" ? "Yes" : "No",
                                                'max_length' => $attributes['max_length'],
                                                'default_value' => $attributes['default_value'],
                                                'allowed_values' => $attributes['allowed_values'],
                                                'lookup_table' => $attributes['lookup_table'],
                                                'depends_on_table_name' => $attributes['depends_on_table_name'],
                                                'depends_on_field_name' => $attributes['depends_on_field_name'],
                                                'depends_on_value' => $attributes['depends_on_value']
                                            );
                                            
                                            $field_registrations[] = $field_registration;
                                        }
                                        
                                        /* We need to make our bundle name available to the sql object
                                         * so the table can be properly registered.
                                         */
                                        $this->store('adapt.installing_bundle', $this->name);
                                        
                                        /* Write the table */
                                        $sql->execute();
                                        
                                        /* Register the table */
                                        $this->data_source->register_table($field_registrations);
                                        $this->remove_store('adapt.installing_bundle');
                                        
                                    }else{
                                        //print "Creating";
                                        /* Create new table */
                                        $field_registrations = array();
                                        
                                        $sql = $this->data_source->sql;
                                        
                                        $sql->create_table($table_name);
                                        
                                        foreach($fields as $field_name => $attributes){
                                            
                                            
                                            $data_type = $attributes['data_type'];
                                            if ($data_type == 'varchar'){
                                                $data_type .= "({$attributes['max_length']})";
                                            }elseif(substr($data_type, 0, 4) == "enum"){
                                                $values = explode("(", $data_type);
                                                $values = explode(")", $values[1]);
                                                $values = $values[0];
                                                
                                                $values = explode(",", $values);
                                                
                                                for($i = 0; $i < count($values); $i++){
                                                    $values[$i] = preg_replace("/'|\"/", "", $values[$i]);
                                                    $values[$i] = sql::q(trim($values[$i]));
                                                    //$values[$i] = sql::q(trim(trim($values[$i], "'"), "\""));
                                                }
                                                
                                                $values = implode(", ", $values);
                                                $attributes['allowed_values'] = "[" . $values . "]";
                                                $attributes['data_type'] = "enum";
                                                //print "<pre>VALUES: {$values}</pre>";
                                                //exit(1);
                                                
                                            }
                                            
                                            $nullable = true;
                                            if (isset($attributes['nullable']) && $attributes['nullable'] == 'No') $nullable = false;
                                            
                                            $default_value = null;
                                            if (isset($attributes['default_value'])) $default_value = $attributes['default_value'];
                                            
                                            $sql->add($field_name, $data_type, $nullable, $default_value);
                                            
                                            if (isset($attributes['primary_key']) && $attributes['primary_key'] == 'Yes'){
                                                $auto_increment = true;
                                                
                                                if (isset($attributes['auto_increment']) && $attributes['auto_increment'] == 'No'){
                                                    $auto_increment = false;
                                                }
                                                $sql->primary_key($field_name, $auto_increment);
                                            }
                                            
                                            if (isset($attributes['index']) && $attributes['index'] == 'Yes'){
                                                $index_size = null;
                                                
                                                if (isset($attributes['index_size'])){
                                                    $index_size = $attributes['index_size'];
                                                }
                                                $sql->index($field_name, $index_size);
                                            }
                                            
                                            if (isset($attributes['referenced_table_name']) && isset($attributes['referenced_field_name'])){
                                                $sql->foreign_key($field_name, $attributes['referenced_table_name'], $attributes['referenced_field_name']);
                                            }
                                            
                                            $field_registration = array(
                                                'bundle_name' => $this->name,
                                                'table_name' => $table_name,
                                                'field_name' => $field_name,
                                                'referenced_table_name' => $attributes['referenced_table_name'],
                                                'referenced_field_name' => $attributes['referenced_field_name'],
                                                'label' => $attributes['label'],
                                                'placeholder_label' => $attributes['placeholder_label'],
                                                'description' => $attributes['description'],
                                                'data_type_id' => array('lookup_from' => 'data_type', 'with_conditions' => ['name' => $attributes['data_type']]),
                                                'primary_key' => $attributes['primary_key'] == "Yes" ? "Yes" : "No",
                                                'signed' => $attributes['signed'] == "Yes" ? "Yes" : "No",
                                                'nullable' => $attributes['nullable'] == "No" ? "No" : "Yes",
                                                'auto_increment' => $attributes['auto_increment'] == "Yes" ? "Yes" : "No",
                                                'timestamp' => $attributes['timestamp'] == "Yes" ? "Yes" : "No",
                                                'max_length' => $attributes['max_length'],
                                                'default_value' => $attributes['default_value'],
                                                'allowed_values' => $attributes['allowed_values'],
                                                'lookup_table' => $attributes['lookup_table'],
                                                'depends_on_table_name' => $attributes['depends_on_table_name'],
                                                'depends_on_field_name' => $attributes['depends_on_field_name'],
                                                'depends_on_value' => $attributes['depends_on_value']
                                            );
                                            
                                            $field_registrations[] = $field_registration;
                                        }
                                        
                                        $sql->add('date_created', 'datetime');
                                        $sql->add('date_modified', 'timestamp');
                                        $sql->add('date_deleted', 'datetime');
                                        
                                        $field_registrations[] = array(
                                            'bundle_name' => $this->name,
                                            'table_name' => $table_name,
                                            'field_name' => 'date_created',
                                            'referenced_table_name' => null,
                                            'referenced_field_name' => null,
                                            'label' => 'Date created',
                                            'placeholder_label' => null,
                                            'description' => 'Date the record was created',
                                            'data_type_id' => array('lookup_from' => 'data_type', 'with_conditions' => ['name' => 'datetime']),
                                            'primary_key' => 'No',
                                            'signed' => 'No',
                                            'nullable' => 'Yes',
                                            'auto_increment' => 'No',
                                            'timestamp' => 'No',
                                            'max_length' => null,
                                            'default_value' => null,
                                            'allowed_values' => null,
                                            'lookup_table' => null,
                                            'depends_on_table_name' => null,
                                            'depends_on_field_name' => null,
                                            'depends_on_value' => null
                                        );
                                        
                                        $field_registrations[] = array(
                                            'bundle_name' => $this->name,
                                            'table_name' => $table_name,
                                            'field_name' => 'date_modified',
                                            'referenced_table_name' => null,
                                            'referenced_field_name' => null,
                                            'label' => 'Date modified',
                                            'placeholder_label' => null,
                                            'description' => 'Date the record was modified',
                                            'data_type_id' => array('lookup_from' => 'data_type', 'with_conditions' => ['name' => 'timestamp']),
                                            'primary_key' => 'No',
                                            'signed' => 'No',
                                            'nullable' => 'Yes',
                                            'auto_increment' => 'No',
                                            'timestamp' => 'Yes',
                                            'max_length' => null,
                                            'default_value' => null,
                                            'allowed_values' => null,
                                            'lookup_table' => null,
                                            'depends_on_table_name' => null,
                                            'depends_on_field_name' => null,
                                            'depends_on_value' => null
                                        );
                                        
                                        $field_registrations[] = array(
                                            'bundle_name' => $this->name,
                                            'table_name' => $table_name,
                                            'field_name' => 'date_deleted',
                                            'referenced_table_name' => null,
                                            'referenced_field_name' => null,
                                            'label' => 'Date deleted',
                                            'placeholder_label' => null,
                                            'description' => 'Date the record was deleted',
                                            'data_type_id' => array('lookup_from' => 'data_type', 'with_conditions' => ['name' => 'datetime']),
                                            'primary_key' => 'No',
                                            'signed' => 'No',
                                            'nullable' => 'Yes',
                                            'auto_increment' => 'No',
                                            'timestamp' => 'No',
                                            'max_length' => null,
                                            'default_value' => null,
                                            'allowed_values' => null,
                                            'lookup_table' => null,
                                            'depends_on_table_name' => null,
                                            'depends_on_field_name' => null,
                                            'depends_on_value' => null
                                        );
                                        
                                        
                                        /* We need to make our bundle name available to the sql object
                                         * so the table can be properly registered.
                                         */
                                        $this->store('adapt.installing_bundle', $this->name);
                                        print "{$sql}\n";
                                        /* Write the table */
                                        $sql->execute();
                                        
                                        if (in_array($table_name, array('data_type', 'field', 'bundle_version'))){
                                            
                                            if (!is_array($this->_schema['add']['records'])){
                                                $this->_schema['add']['records'] = array();
                                            }
                                            
                                            if (!is_array($this->_schema['add']['records']['field'])){
                                                $this->_schema['add']['records']['field'] = array();
                                            }
                                            
                                            $this->_schema['add']['records']['field'] = array_merge($this->_schema['add']['records']['field'], $field_registrations);
                                            
                                        }else{
                                            /* Register the table */
                                            $this->data_source->register_table($field_registrations);
                                        }
                                        
                                        $this->remove_store('adapt.installing_bundle');
                                    }
                                }
                            }
                        }
                        
                        if (is_array($this->_schema['add']['records'])){
                            /*
                             * Adding records
                             */
                            $tables = array_keys($this->_schema['add']['records']);
                            
                            foreach($tables as $table_name){
                                print "========= HANDLING: {$table_name} ==========\n";
                                $rows = $this->_schema['add']['records'][$table_name];
                                print_r($rows);
                                $field_names = array();
                                
                                $schema = $this->data_source->get_row_structure($table_name);
                                
                                if (is_null($schema) || !is_array($schema)){
                                    
                                    if (isset($this->_schema['add']['records']['field'])){
                                        $schema = array();
                                        
                                        foreach($this->_schema['add']['records']['field'] as $field){
                                            if ($field['table_name'] == $table_name){
                                                $schema[] = $field;
                                            }
                                        }
                                    }
                                }
                                
                                if (is_array($schema)){
                                    foreach($schema as $field){
                                        $field_names[] = $field['field_name'];
                                    }
                                    print "FIELD NAMES: " . print_r($field_names, true) . "\n";
                                    foreach($rows as $row){
                                        $values = [];
                                        
                                        /*
                                         * Before we can proceed we need to resolve the lookups
                                         */
                                        foreach($field_names as $field_name){
                                            $value = $row[$field_name];
                                            
                                            if (is_array($value) && isset($value['lookup_from']) && isset($value['with_conditions'])){
                                                $sql = $this->data_source->sql
                                                    ->select($value['lookup_from'] . '_id')
                                                    ->from($value['lookup_from']);
                                                    
                                                $where = new sql_and(
                                                    new sql_cond('date_deleted', sql::IS, new sql_null())
                                                );
                                                
                                                foreach($value['with_conditions'] as $condition => $val){
                                                    $where->add(new sql_cond($condition, sql::EQUALS, sql::q($val)));
                                                }
                                                
                                                $sql->where($where);
                                                print "{$sql}\nFIELD: {$field_name}\nTABLE: {$table_name}\nBUNDLE: {$this->name}\n";
                                                $results = $sql->execute(60 * 60 * 24 * 5)->results();
                                                $errors = $sql->errors(true);
                                                
                                                if (count($errors)){
                                                    foreach($errors as $error){
                                                        $this->error($error);
                                                    }
                                                    
                                                    return false;
                                                }
                                                
                                                if (count($results) == 0){
                                                    $this->error("Unable to lookup value for field {$field_name}");
                                                    return false;
                                                }elseif(count($results) > 1){
                                                    $this->error("Multiple values found when looking up value for field {$field_name}");
                                                    return false;
                                                }
                                                $row[$field_name] = $results[0][$value['lookup_from'] . "_id"];
                                            }
                                        }
                                        
                                        /*
                                         * We need to check if a row exists, if it has a name
                                         * field we will use this as a key and update the rest
                                         * of the row, if it doesn't then we are going to try
                                         * and match the whole record, if it matches we will
                                         * ignore the entire record, if it doesn't we will
                                         * insert it.
                                         */
                                        $ignore_record = false;
                                        
                                        if (in_array('name', $field_names)){
                                            // Intentionally we are skipping the
                                            // date_deleted field on the basis that
                                            // if this record is deleted, it should
                                            // remain deleted, that said, still
                                            // we will update it as required.
                                            $sql = $this->data_source->sql
                                                ->select($table_name . '_id')
                                                ->from($table_name)
                                                ->where(new sql_cond('name', sql::EQUALS, sql::q($row['name'])));
                                            print "UPDATE CHECK: {$sql}\n";
                                            $results = $sql->execute(60 * 60 * 24 * 7)->results();
                                            
                                            if (count($results) == 1){
                                                // Update the record
                                                $ignore_record = true;
                                                
                                                $sql = $this->data_source->sql;
                                                $sql->update($table_name);
                                                foreach($field_names as $field_name){
                                                    if ($row[$field_name]){
                                                        $sql->set($field_name, sql::q($row[$field_name]));
                                                    }
                                                }
                                                $sql->where(new sql_cond($table_name, sql::EQUALS, sql::q($row[$field_name])));
                                                print "UPDATE: {$sql}\n";
                                                $sql->execute();
                                            }
                                        }else{
                                            // Try to match against all fields
                                            $sql = $this->data_source->sql
                                                ->select($table_name . '_id')
                                                ->from($table_name);
                                            
                                            $where = new sql_and();
                                            foreach($field_names as $field_name){
                                                if ($row[$field_name]){
                                                    $where->add(new sql_cond($field_name, sql::EQUALS, sql::q($row[$field_name])));
                                                }
                                            }
                                            print "MATCH FULL: {$sql}\n";
                                            if (count($sql->execute(60 * 60 * 24 * 7)->results()) == 1){
                                                $ignore_record = true;
                                            }
                                        }
                                        
                                        if (!$ignore_record){
                                            // Insert the record
                                            foreach($field_names as $field_name){
                                                switch($field_name){
                                                case "date_created":
                                                case "date_modified":
                                                    $row[$field_name] = new sql_now();
                                                    break;
                                                case "guid":
                                                    $row[$field_name] = guid();
                                                    break;
                                                case "bundle_name":
                                                    $row[$field_name] = $this->name;
                                                    break;
                                                }
                                                
                                                $values[] = $row[$field_name];
                                            }
                                            
                                            $sql = $this->data_source->sql;
                                            print_r($field_names);
                                            print_r($values);
                                            $sql->insert_into($table_name, $field_names)->values($values);
                                            print "INSERT: {$sql}\n";
                                            $sql->execute();
                                            $errors = $sql->errors(true);
                                            
                                            if (count($errors)){
                                                foreach($error as $errors) $this->error($error);
                                                return false;
                                            }
                                            
                                            if ($table_name == 'data_type' || $table_name == 'field'){
                                                $this->data_source->load_schema();
                                            }
                                        }
                                        
                                    }
                                } else {
                                    $this->error("Unable to find schema for {$table_name}");
                                    return false;
                                }
                            }
                        }
                    }
                    
                    if (is_array($this->_schema['remove'])){
                        /*
                         * Lets remove from the schema
                         */
                        
                    }
                }
                
                // Process config handlers
                //$handlers = $this->store("adapt.config_handlers") ?: [];
                //foreach($handlers as $tag => $handler){
                //    print "<pre>Handling tag {$tag}:{$handler['bundle_name']}:{$handler['function']}</pre>";
                //    $bundle = $this->bundles->load_bundle($handler['bundle_name']);
                //    if ($bundle instanceof bundle && $bundle->name == $handler['bundle_name']){
                //        $function = $handler['function'];
                //        
                //        if (method_exists($bundle, $function)){
                //            foreach($handler['actions'] as $child){
                //                $bundle->$function($this, $child);
                //            }
                //            unset($handler['actions']);
                //        }
                //        
                //    }
                
                /* Process config handlers */
                $handlers = $this->store("adapt.config_handlers") ?: [];
                foreach($this->_config_handlers_to_process as $tag => $children){
                    //print "<pre>Seeking handler for {$tag}</pre>";
                    //print "<pre>" . print_r($handlers, true) . "</pre>";
                    if (is_array($handlers[$tag])){
                        //print "<pre>foo</pre>";
                        $bundle = $this->bundles->load_bundle($handlers[$tag]['bundle_name']);
                        if ($bundle instanceof bundle && $bundle->name == $handlers[$tag]['bundle_name']){
                            //print "<pre>bar</pre>";
                            $function = $handlers[$tag]['function'];
                            
                            if (method_exists($bundle, $function)){
                                foreach($children as $child){
                                    //print "<pre>foobar {$child->tag}</pre>";
                                    $bundle->$function($this, $child);
                                }
                            }
                        }
                    }
                
                }
                
                //print "<pre>" . print_r($this->_schema, true) . "</pre>";
//<<<<<<< HEAD
                if ($this->data_source && $this->data_source instanceof data_source_sql){   
                    /* Add the bundle to bundle_version if it isn't already */
                    $model = new model_bundle_version();
                    if (!$model->load_by_name_and_version($this->name, $this->version)){
                        $errors = $model->errors(true);
                        //print "<pre>" . print_r($errors, true) . "</pre>";
                        foreach($errors as $error) $this->error("Model 'bundle_version' return the error \"{$error}\"");
                    }
//=======
//                
//                /* Add the bundle to bundle_version if it isn't already */
//                $model = new model_bundle_version();
//                if (!$model->load_by_name_and_version($this->name, $this->version)){
//                    $errors = $model->errors(true);
//                    //print "<pre>" . print_r($errors, true) . "</pre>";
//                    foreach($errors as $error) $this->error("Model 'bundle_version' return the error \"{$error}\"");
//                }
//                
//                $model->name = $this->name;
//                $model->type = $this->type;
//                $model->version = $this->version;
//                $model->local = "Yes";
//                $model->installed = "Yes";
//                if ($model->save()){
//                    $errors = $model->errors(true);
//                    //print "<pre>" . print_r($errors, true) . "</pre>";
//                    //print "<pre>Saved {$this->name}-{$this->version}</pre>";
//                    $this->_is_installed = true;
//                    $this->bundles->set_bundle_installed($this->name, $this->version);
//>>>>>>> horizon
                    
                    $model->name = $this->name;
                    $model->type = $this->type;
                    $model->version = $this->version;
                    $model->local = "Yes";
                    $model->installed = "Yes";
                    if ($model->save()){
                        $errors = $model->errors(true);
                        print "<pre>" . print_r($errors, true) . "</pre>";
                        print "<pre>Saved {$this->name}-{$this->version}</pre>";
                        $this->_is_installed = true;
                        $this->bundles->set_bundle_installed($this->name, $this->version);
                        
                        /* Process install handlers */
                        $handlers = $this->store('adapt.install_handlers');
                        //print "<pre>Handlers: " . print_r($handlers, true) . "</pre>";
                        if (is_array($handlers) && is_array($handlers[$this->name])){
                            foreach($handlers[$this->name] as $handler){
                                $bundle = $this->bundles->load_bundle($handler['bundle_name']);
                                if ($bundle instanceof bundle){
                                    $function = $handler['function'];
                                    if (method_exists($bundle, $function)){
                                        $bundle->$function($this);
                                    }
                                }
                            }
                        }
                        
                        return true;
                    }else{
                        $errors = $model->errors(true);
                        //print "<pre>" . print_r($errors, true) . "</pre>";
                        foreach($errors as $error) $this->error("Model 'bundle_version' returned the error \"{$error}\"");
                        return false;
                    }
                }
                
                /* Remove installation mark */
                $this->file_store->delete("adapt/installation/{$this->name}-{$this->version}");
            }
        }
        
        /**
         * Is the bundle installed?
         *
         * @access public
         * @return boolean
         */
        public function is_installed(){
            /*
             * self::_is_installed property is only used by
             * this function 
             */
            
            if (is_null($this->_is_installed)){
                if ($this->is_loaded){
                    $this->_is_installed = $this->bundles->is_bundle_installed($this->name, $this->version);
                }else{
                    return false;
                }
            }
            
            return $this->_is_installed;
        }
        
        /**
         * Is the bundle currently being installed?
         *
         * @access public
         * @return boolean
         */
        public function is_installing(){
            if ($this->file_store->get("adapt/installation/{$this->name}-{$this->version}") == "true"){
                return true;
            }
            
            return false;
        }
    }
}