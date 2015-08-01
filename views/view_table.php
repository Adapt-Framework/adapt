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

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    //TODO: Field formatting - Text footer
    
    class view_table extends view{
        
        protected $_header;
        protected $_body;
        protected $_footer;
        
        protected $_col_types = array();
        
        const TEXT = "text";
        const NUMERIC = "numeric";
        const CURRENCY = "currency";
        
        const AVERAGE = 'average';
        const COUNT = 'count';
        const SUM = 'sum';
        const MAX = 'max';
        const MIN = 'min';
        
        
        public function __construct($data = array(), $show_header = true, $show_footer = false){
            parent::__construct('table');
            $this->_header = new html_thead();
            $this->_body = new html_tbody();
            $this->_footer = new html_tfoot();
            
            parent::add($this->_body);
            
            $this->add($data);
            $this->show_header = $show_header;
            $this->show_footer = $show_footer;
        }
        
        public function pget_row_count(){
            return $this->_body->count();
        }
        
        public function pget_col_count(){
            $rows = $this->_body->get();
            
            $col_count = 0;
            
            if (is_array($rows) && count($rows)){
                $row = $rows[0];
                if ($row instanceof html_tr){
                    $cells = $row->get();
                    foreach($cells as $cell){
                        if ($cell instanceof html_td){
                            
                            $span = $cell->attr('colspan');
                            if (!is_null($span) && is_numeric($span)){
                                $col_count += $span;
                            }else{
                                $col_count++;
                            }
                        }
                    }
                }
            }
            
            return $col_count;
        }
        
        public function pget_show_header(){
            return $this->find('thead')->size() ? true : false;
        }
        
        public function pset_show_header($value){
            if ($value == true){
                if ($this->show_header == false){
                    $a = new aquery($this);
                    $a->prepend($this->_header);
                }
            }else{
                if ($this->show_header == true){
                    $this->find('thead')->detach();
                }
            }
        }
        
        public function pget_show_footer(){
            return $this->find('tfoot')->size() ? true : false;
        }
        
        public function pset_show_footer($value){
            if ($value == true){
                if ($this->show_footer == false){
                    $a = new aquery($this);
                    $a->append($this->_footer);
                }
            }else{
                if ($this->show_footer == true){
                    $this->find('tfoot')->detach();
                }
            }
        }
        
        public function set_footer($col_index, $type = self::SUM, $text_value = null){
            /* Do we have a footer? */
            $this->show_footer = true;
            
            if ($this->_footer->count() == 0){
                /* Build the footer */
                
                /* We can only do this if we have at least one row */
                $rows = $this->_body->get();
                
                if (is_array($rows) && count($rows)){
                    $row = $rows[0];
                    $col_count = 0;
                    if ($row instanceof html_tr){
                        $cells = $row->get();
                        foreach($cells as $cell){
                            if ($cell instanceof html_td){
                                
                                $span = $cell->attr('colspan');
                                if (!is_null($span) && is_numeric($span)){
                                    $col_count += $span;
                                }else{
                                    $col_count++;
                                }
                            }
                        }
                    }
                    
                    $row = new html_tr();
                    for($i = 0; $i < $col_count; $i++){
                        $row->add(new html_td('&nbsp;'));
                    }
                    $this->_footer->add($row);
                }
            }
            
            /* Do we have a footer now? */
            if ($this->_footer->count()){
                $row = $this->_footer->get(0);
                if ($row instanceof html_tr){
                    $cell = $row->get($col_index);
                    if ($cell instanceof html_td){
                        /* Lets get all the cells we need to calculate */
                        $output = 0;
                        $count = 0;
                        
                        if ($type != self::TEXT){
                            for($i = 0; $i < $this->row_count; $i++){
                                $c = $this->get_cell($col_index, $i);
                                $value = $c->value();
                                switch($type){
                                case self::AVERAGE:
                                case self::SUM:
                                    $output += intval($value);
                                    $count++;
                                    break;
                                case self::COUNT:
                                    if ($value != ""){
                                        $output += 1;
                                    }
                                    break;
                                case self::MAX:
                                    if ($value > $output) $output = $value;
                                    break;
                                case self::MIN:
                                    if ($i == 0){
                                        $output = $value;
                                    }elseif($output > $value){
                                        $output = $value;
                                    }
                                    break;
                                }
                            }
                            
                            if ($type == self::AVERAGE){
                                $output = $output / $count;
                            }
                        }else{
                            $output = $text_value;
                        }
                        
                        
                        $cell->clear();
                        $cell->add($output);
                        $cell->add_class($type);
                    }
                }
            }
        }
        
        public function set_column_type($col_index, $type = self::TEXT){
            $this->_col_types[$col_index] = $type;
            
            $rows = $this->_body->get();
            if (is_array($rows)){
                foreach($rows as $row){
                    if ($row instanceof html_tr){
                        $cells = $row->get();
                        if (is_array($cells) && count($cells) > $col_index){
                            if ($cells[$col_index] instanceof html_td){
                                $cells[$col_index]->remove_class(self::CURRENCY);
                                $cells[$col_index]->remove_class(self::NUMERIC);
                                $cells[$col_index]->remove_class(self::TEXT);
                                $cells[$col_index]->add_class(self::TEXT);
                            }
                        }
                    }
                }
            }
            
            $rows = $this->_header->get();
            if (is_array($rows)){
                foreach($rows as $row){
                    if ($row instanceof html_tr){
                        $cells = $row->get();
                        if (is_array($cells) && count($cells) > $col_index){
                            if ($cells[$col_index] instanceof html_th){
                                $cells[$col_index]->remove_class(self::CURRENCY);
                                $cells[$col_index]->remove_class(self::NUMERIC);
                                $cells[$col_index]->remove_class(self::TEXT);
                                $cells[$col_index]->add_class(self::TEXT);
                            }
                        }
                    }
                }
            }
            
            $rows = $this->_footer->get();
            if (is_array($rows)){
                foreach($rows as $row){
                    if ($row instanceof html_tr){
                        $cells = $row->get();
                        if (is_array($cells) && count($cells) > $col_index){
                            if ($cells[$col_index] instanceof html_td){
                                $cells[$col_index]->remove_class(self::CURRENCY);
                                $cells[$col_index]->remove_class(self::NUMERIC);
                                $cells[$col_index]->remove_class(self::TEXT);
                                $cells[$col_index]->add_class(self::TEXT);
                            }
                        }
                    }
                }
            }
        }
        
        public function get_column_type($col_index){
            if (isset($this->_col_types[$col_index])){
                return $this->_col_types[$col_index];
            }
            
            return self::TEXT;
        }
        
        public function merge_cells($col_index, $row_index, $col_span = 1, $row_span = 1){
            $rows = $this->_body->get();
            
            if (is_array($rows) && count($rows) > $row_index && count($rows) > ($row_index + $row_span - 1)){
                
                $row = $rows[$row_index];
                if ($row instanceof html_tr){
                    $cells = $row->get();
                    
                    if (is_array($cells) && count($cells) > $col_index && count($cells) > ($col_index + $col_span -1)){
                        $cell = $cells[$col_index];
                        if ($cell instanceof html_td){
                            if ($col_span > 1){
                                $cell->attr('colspan', $col_span);
                                for($i = $col_index + 1; $i < ($col_index + $col_span); $i++){
                                    $row->remove($i);
                                }
                            }
                            
                            if ($row_span > 1){
                                $cell->attr('rowspan', $row_span);
                                for($i = $row_index + 1; $i < ($row_index + $row_span); $i++){
                                    $rows[$i]->remove($col_index);
                                }
                            }
                        }
                    }
                }
                
            }
        }
        
        public function get_cell($col_index, $row_index){
            if ($col_index < $this->col_count && $row_index < $this->row_count){
                $row = $this->_body->get($row_index);
                if ($row instanceof html_tr){
                    
                    $cells = $row->get();
                    
                    $offset = 0;
                    foreach($cells as $cell){
                        if ($offset == $col_index) return $cell;
                        
                        if ($cell instanceof html_td){
                            $span = $cell->attr('colspan');
                            if (!is_null($span) && is_numeric($span)){
                                $offset += $span;
                            }else{
                                $offset++;
                            }
                        }
                    }
                }
            }
            
            return null;
        }
        
        public function add_row($data){
            if (is_array($data)){
                $row = new html_tr();
                
                if (is_assoc($data)){
                    $add_header = false;
                    $head_row = new html_tr();
                    
                    if ($this->_header->find('tr')->size() == 0){
                        $add_header = true;
                    }
                    $index = 0;
                    
                    foreach($data as $key => $cell){
                        $td = new html_td($cell);
                        $td->add_class($this->get_column_type($index));
                        $row->add($td);
                        
                        if ($add_header){
                            $th = new html_th($key);
                            $th->add_class($this->get_column_type($index));
                            $head_row->add($th);
                        }
                        
                        $index++;
                    }
                    
                    if ($add_header){
                        $this->_header->add($head_row);
                    }
                    
                }else{
                    $index = 0;
                    foreach($data as $cell){
                        $td = new html_td($cell);
                        $td->add_class($this->get_column_type($index));
                        $row->add($td);
                    }
                    $index++;
                }
                
                $this->_body->add($row);
            }
        }
        
        public function add($data = array()){
            
            if (is_array($data)){
                foreach($data as $row){
                    $this->add_row($row);
                }
            }
            
        }
    }
    
}

?>