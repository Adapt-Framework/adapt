<?php

/**
 * Adapt Framework
 *
 * The MIT License (MIT)
 *   
 * Copyright (c) 2017 Matt Bruton
 * Authored by Matt Bruton (matt.bruton@gmail.com)
 * Authored by Joe Hockaday (jdhockad@hotmail.com)
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
 * @author      Joe Hockaday <jdhockad@hotmail.com>
 * @copyright   2017 Matt Bruton <matt.bruton@gmail.com>
 * @license     https://opensource.org/licenses/MIT     MIT License
 * @link        http://www.adpatframework.com
 */

namespace adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    /**
     * PostgreSQL Data source driver
     */
    class data_source_postgresql extends data_source_sql implements interfaces\data_source_sql{
        
        /**
         * Holds the SQL string of the last rendered insert
         * @access protected
         */
        protected $_last_rendered_insert_statement;
        
        /**
         * Holds the last insert id
         * @access protected
         */
        protected $_last_insert_id;
        
        /**
         * Execute a SQL read or write statement.
         *
         * @access public
         * @param string
         * A SQL statement.
         * @param boolean
         * Is this statement writing data?
         * @return boolean|resource
         * Returns a statement handle.
         */
        public function query($sql, $write = false){
            $host = $this->get_host($write);
            $this->_last_insert_id = null;
            
            if (!is_null($host) && isset($host['handle'])){
                if ($result = pg_query($host['handle'], $sql)){
                    
                    if (!is_null($this->_last_rendered_insert_statement) && $sql == $this->_last_rendered_insert_statement){
                        
                        $this->_last_rendered_insert_statement = null;
                        $results = $this->fetch($result, data_source_sql::FETCH_ALL_ASSOC);
                        
                        if (count($results) == 1 && isset($results[0]['id'])){
                            $this->_last_insert_id = $results[0]['id'];
                        }
                    }
                    
                    $this->trigger(self::EVENT_QUERY, array('sql' => $sql, 'host' => $host));
                    if ($write){
                        return true;
                    }elseif ($result/* = pg_result($host['handle'])*/){
                        return $result;
                    }
                    
                    return false;
                }else{
                    $error = pg_last_error($host['handle']);
                    $this->error($error);
                    $this->error("Invalid SQL statement: {$sql}");
                    
                    return false;
                }
            }
            
            $this->error("No hosts available to query");
            return false;
        }
        
        /**
         * Fetches data from a statement handle.
         *
         * @access public
         * @param resource
         * The statement handle returned from read(), write() or query().
         * @param integer
         * How should the data be fetched? See the constants prefixed FETCH_
         * @return boolean|array
         */
        public function fetch($statement_handle, $fetch_type = self::FETCH_ASSOC){
            if ($statement_handle){
                switch($fetch_type){
                case self::FETCH_ASSOC:
                    return pg_fetch_assoc($statement_handle);
                case self::FETCH_ARRAY:
                    return pg_fetch_array($statement_handle);
                    return $statement_handle->fetch_array();
                case self::FETCH_OBJECT:
                    return pg_fetch_object($statement_handle);
                case self::FETCH_ALL_ASSOC:
                    $results = array();
                    while($row = pg_fetch_assoc($statement_handle)){
                        $results[] = $row;
                    }
                    return $results;
                case self::FETCH_ALL_ARRAY:
                    $results = array();
                    while($row = pg_fetch_array($statement_handle)){
                        $results[] = $row;
                    }
                    return $results;
                case self::FETCH_ALL_OBJECT:
                    $results = array();
                    while($row = pg_fetch_object($statement_handle)){
                        $results[] = $row;
                    }
                    return $results;
                }
            }
            
            $this->error("Invalid statement handle");
            return false;
        }
        
        /**
         * Returns the last inserted record ID
         *
         * @access public
         * @param string The table name
         * @return integer
         * The ID of the record.
         */
        public function last_insert_id(){
            return $this->_last_insert_id;
        }
        
        /**
         * Builds a connection string
         * 
         * @access public
         * @param array The host record
         * @param bool Should the string include the database name
         * @return string
         */
        public function connection_string($host, $include_database_name = true){
            $connection_string = "";
            
            if (isset($host['host'])){
                $connection_string = "host={$host['host']}";
            }
            
            if (isset($host['port'])){
                $connection_string .= " port={$host['port']}";
            }
            
            if (isset($host['username'])){
                $connection_string .= " user={$host['username']}";
            }
            
            if (isset($host['password'])){
                $connection_string .= " password={$host['password']}";
            }
            
            if ($include_database_name && isset($host['schema'])){
                $connection_string .= " dbname={$host['schema']}";
            }
            
            $encoding = $this->setting('postgresql.default_character_set');
            if (!is_null($encoding)){
                $connection_string .= " options='--client_encoding={$encoding}'";
            }
            
            return trim($connection_string);
        }
        
        /**
         * Opens a connection to the $host.
         *
         * Connections are handled automatically and you shouldn't
         * need to use this function.
         *
         * Example usage.
         * <code>
         * $source = new data_source_postgresql();
         * $source->add_host('hostname', 'username', 'password', 'database', 5432, true);
         * $source->connect($source->get_host(true));
         * </code>
         *
         * @access public
         * @param array
         * An array representing the host.
         * @return boolean|mixed
         */
        public function connect($host){
            $connection_string = $this->connection_string($host);
            
            $postgresql = pg_connect($connection_string);
            
            if (pg_connection_status($postgresql) !== PGSQL_CONNECTION_OK){
                /* The database may not exist, so we are going to attempt
                 * the connection again without the database name
                 */
                $connection_string = $this->connection_string($host, false);
                $postgresql = pg_connect($connection_string);
                
                if (pg_connection_status($postgresql) !== PGSQL_CONNECTION_OK){
                    $this->error("Unable to connect to the postgresql instance");
                    return false;
                }
                
                /*
                 * Lets attempt to create the database
                 */
                if (pg_query($postgresql, "CREATE DATABASE {$host['schema']};") === false){
                    $this->error("Unable to create database '{$host['schema']}'");
                    return false;
                }
                
                /*
                 * Lets reconnect with our database param to check all is good
                 */
                $postgresql = pg_connect($this->connection_string($host));
                
                if (pg_connection_status($postgresql) === PGSQL_CONNECTION_BAD){
                    $this->error("Unable to connect to database");
                    return false;
                }
            }
            
            $this->trigger(self::EVENT_HOST_CONNECT, array('host' => $host));
            return $postgresql;
        }
        
        /**
         * Closes a connection to the $host.
         *
         * Connections are handled automatically and you shouldn't
         * need to use this function.
         *
         * Example usage.
         * <code>
         * $source = new data_source_mysql();
         * $source->add_host('hostname', 'username', 'password', 'schema', 3306, true);
         * $source->disconnect($source->get_host(true));
         * </code>
         *
         * @access public
         * @param array
         * An array representing the host.
         */
        public function disconnect($host){
            if (isset($host['handle'])){
                pg_close($host['handle']);
                $this->trigger(self::EVENT_HOST_DISCONNECT, array('host' => $host));
            }
        }

        /**
         * Escapes a value
         *
         * @access public
         * @param string
         * The value to be escaped
         * @return string
         * The escaped value
         */
        public function escape($string){
            $host = $this->get_host();
            if (isset($host['handle'])){
                return pg_escape_string($host['handle'], $string);
            }else{
                return parent::escape($string);
            }
        }

        /**
         * Converts a sql object to a SQL string for the target
         * database platform.
         *
         * @access public
         * @param sql
         * The sql object to be converted.
         * @return string
         * Returns the SQL statement as a string.
         */
        public function render_sql(\adapt\sql $sql){
            $statement = "";

            if ($sql instanceof \adapt\sql){
                if (!is_null($sql->statement)){
                    if ($statement == ""){
                        $statement = $sql->statement;
                    }else{
                        $statement .= "(" . $sql->statement . ")";
                    }
                    
                    return $statement;
                }
                
                if (is_array($sql->functions) && count($sql->functions)){
                    $keys = array_keys($sql->functions);
                    $function_name = $keys[0];
                    $params = $sql->functions[$function_name];
                    
                    switch($function_name){
                    case "union":
                        foreach($params as $param){
                            if ($param instanceof \adapt\sql){
                                if ($statement == ""){
                                    $statement = "(" . $param . ")";
                                }else{
                                    $statement .= " UNION (" . $param . ")";
                                }
                            }
                        }

                        $statement .= "\n";

                        // Add ordering to UNION
                        $ordering = $sql->ordering;
                        if (is_array($ordering) && count($ordering)){
                            $statement .= "ORDER BY ";
                            $first = true;
                            foreach($ordering as $order){
                                if (!$first) $statement .= ",\n";
                                if ($order['field'] instanceof sql){
                                    $statement .= $this->render_sql($order['field']);
                                }else{
                                    $statement .= $order['field'];
                                }

                                if ($order['ascending']){
                                    $statement .= " ASC";
                                }else{
                                    $statement .= " DESC";
                                }

                                $first = false;
                            }

                            $statement .= "\n";

                            // Add limit to UNION
                            $limit = $sql->limit_count;
                            $offset = $sql->limit_offset;
                            if (!is_null($limit)){
                                $statement .= "LIMIT {$limit}\n";
                                if (isset($offset)){
                                    $statement .= "OFFSET {$offset}\n";
                                }
                            }
                            $statement .= "\n";
                        }

                        break;
                    case "and":
                    case "or":
                        foreach($params as $param){
                            
                            if ($param instanceof \adapt\sql){
                                $param = $this->render_sql($param);
                                //$param = $param->render();
                            }
                            
                            if ($statement == ""){
                                $statement .= "(";
                                $statement .= $param;
                            }else{
                                $statement .= " " . strtoupper($function_name) . " ";
                                $statement .= $param;
                            }
                            
                        }
                        
                        $statement .= ")";
                        break;
                    case "between":
                        if (count($params) == 3){
                            if ($params[0] instanceof sql) $params[0] = $this->render_sql($params[0]);
                            if ($params[1] instanceof sql) $params[1] = $this->render_sql($params[1]);
                            if ($params[2] instanceof sql) $params[2] = $this->render_sql($params[2]);
                            
                            $statement .= "({$params[0]} BETWEEN {$params[1]} AND {$params[2]})";
                        }
                        break;
                    case "condition":
                    case "cond":
                        if (count($params) == 3){
                            if ($params[0] instanceof sql) $params[0] = $this->render_sql($params[0]);
                            if ($params[1] instanceof sql) $params[1] = $this->render_sql($params[1]);
                            if ($params[2] instanceof sql) $params[2] = $this->render_sql($params[2]);
                            
                            $statement .= "{$params[0]} {$params[1]} {$params[2]}";
                        }
                        break;
                    case "if":
                        if (count($params) == 3){
                            if ($params[0] instanceof sql) $params[0] = $this->render_sql($params[0]);
                            if ($params[1] instanceof sql) $params[1] = $this->render_sql($params[1]);
                            if ($params[2] instanceof sql) $params[2] = $this->render_sql($params[2]);
                            
                            $statement .= "CASE WHEN {$params[0]} THEN {$params[1]} ELSE {$params[2]} END";
                        }
                        break;
                    case "abs":
                    case "ceil":
                    case "exp":
                    case "floor":
                    case "log":
                    case "power":
                    case "round":
                    case "sign":
                    case "ascii":
                    case "char":
                    case "concat":
                    case "format":
                    case "length":
                    case "ltrim":
                    case "replace":
                    case "reverse":
                    case "rtrim":
                    //case "substring":
                    case "substr":
                    case "upper":
                    case "current_date":
                    case "current_time":
                    case "current_datetime":
                    //case "current_timestamp":
                    case "now":
                        $statement = strtoupper($function_name) . "(";
                        $first = true;
                        foreach($params as $param){
                            if ($param instanceof sql){
                                $param = $param = $this->render_sql($param);
                            }
                            
                            if ($first){
                                $statement .=  $param;
                                $first = false;
                            }else{
                                $statement .= ", " . $param;
                            }
                        }
                        
                        $statement .= ")";
                        return $statement;
                        break;
                    case "null":
                    case "true":
                    case "false":
                        return strtoupper($function_name);
                    }
                    
                    
                    
                    return $statement;
                }
                
                /* Insert statement */
                if(!is_null($sql->insert_into_table_name)){
                    return $this->render_sql_insert($sql);
                }
                
                /* Select Statement */
                if (is_array($sql->select_fields) && count($sql->select_fields)){
                    $select_fields = $sql->select_fields;
                    $statement = "SELECT\n";
                    if ($sql->is_distinct) $statement = "SELECT DISTINCT\n";
                    $first = true;
                    foreach($select_fields as $pair){
                        $alias = null;
                        $field = $pair['value'];
                        if (isset($pair['alias'])) $alias = $pair['alias'];
                        
                        if (!$first) $statement .= ",\n";
                        if ($field instanceof \adapt\sql){
                            $statement .= $this->render_sql($field);
                        }else{
                            $statement .= $field;
                        }
                        
                        if (!is_null($alias) && (!is_string($field) || $field != $alias)){
                            $statement .= " AS " . sql::q($alias);
                        }
                        
                        $first = false;
                    }
                    
                    $statement .= "\n";
                    
                    /* From */
                    $from = $sql->from_fields;
                    if (is_array($from) && count($from)){
                        if (is_assoc($from)){
                            $keys = array_keys($from);
                            $statement .= "FROM ";
                            $value = $from[$keys[0]];
                            if ($value instanceof \adapt\sql){
                                $statement .= "(" . $this->render_sql($value) . ")";
                            }else{
                                $statement .= $value;
                            }
                            
                            $statement .= " AS {$keys[0]}\n";
                        }else{
                            $statement .= "FROM {$from[0]}\n";
                        }
                    }
                    
                    /* Joins */
                    $joins = $sql->join_conditions;
                    if (is_array($joins) && count($joins)){
                        foreach($joins as $join){
                            switch($join['type']){
                            case \adapt\sql::LEFT_JOIN:
                                $statement .= "LEFT JOIN ";
                                break;
                            case \adapt\sql::RIGHT_JOIN:
                                $statement .= "RIGHT JOIN ";
                                break;
                            case \adapt\sql::INNER_JOIN:
                                $statement .= "INNER JOIN ";
                                break;
                            case \adapt\sql::OUTER_JOIN:
                                $statement .= "OUTER JOIN ";
                                break;
                            case \adapt\sql::JOIN:
                            default:
                                $statement .= "JOIN ";
                            }
                            
                            if ($join['table'] instanceof \adapt\sql){
                                $statement .= "(" . $this->render_sql($join['table']) . ")";
                            }else{
                                $statement .= $join['table'];
                            }
                            
                            if (!is_null($join['alias']) && $join['alias'] != ""){
                                $statement .= " AS {$join['alias']}\n";
                            }
                            
                            if ($join['condition'] instanceof \adapt\sql){
                                $statement .= "ON " . $this->render_sql($join['condition']) . "\n";
                            }elseif(is_string($join['condition']) && $join['condition'] != ""){
                                $statement .= "USING({$join['condition']})\n";
                            }
                        }
                    }
                    
                    /* Where */
                    $where = $sql->where_conditions;
                    if (isset($where) && count($where)){
                        $statement .= "WHERE ";
                        foreach($where as $item){
                            if ($item instanceof sql){
                                $statement .= $this->render_sql($item);
                            }else{
                                $statement .= $item;
                            }
                        }
                    }
                    $statement .= "\n";

                    /* Grouping */
                    $grouping = $sql->grouping;
                    if (is_array($grouping) && count($grouping)){
                        $statement .= "GROUP BY ";
                        $first = true;
                        foreach($grouping as $group){
                            if (!$first) $statement .= ",\n";
                            if ($group['field'] instanceof sql){
                                $statement .= $this->render_sql($group['field']);
                            }else{
                                $statement .= $group['field'];
                            }
                            
                            if ($group['with_rollup']){
                                $statement .= " WITH ROLLUP";
                            }
                            
                            $first = false;
                        }
                        
                        $statement .= "\n";
                    }

                    /* Having */
                    $having = $sql->having_conditions;
                    if (isset($having) && count($having)){
                        $statement .= "HAVING ";
                        foreach($having as $item){
                            if ($item instanceof sql){
                                $statement .= $this->render_sql($item);
                            }else{
                                $statement .= $item;
                            }
                        }
                    }
                    $statement .= "\n";

                    /* Ordering */
                    $ordering = $sql->ordering;
                    if (is_array($ordering) && count($ordering)){
                        $statement .= "ORDER BY ";
                        $first = true;
                        foreach($ordering as $order){
                            if (!$first) $statement .= ",\n";
                            if ($order['field'] instanceof sql){
                                $statement .= $this->render_sql($order['field']);
                            }else{
                                $statement .= $order['field'];
                            }

                            if ($order['ascending']){
                                $statement .= " ASC";
                            }else{
                                $statement .= " DESC";
                            }
                            
                            $first = false;
                        }
                        
                        $statement .= "\n";
                    }

                    /* Limit */
                    $limit = $sql->limit_count;
                    $offset = $sql->limit_offset;
                    if (!is_null($limit)){
                        $statement .= "LIMIT {$limit}\n";
                        if (isset($offset)){
                            $statement .= "OFFSET {$offset}\n";
                        }
                    }
                    
                    return $statement;
                }
                
                /* Update statement */
                if (count($sql->update_tables) > 0){
                    /* Update */
                    $statement = "UPDATE ";
                    $tables = $sql->update_tables;
                    $first = true;
                    foreach($tables as $key => $value){
                        if (!$first) $statement .= ", ";
                        
                        if (is_int($key)){
                            $value = $this->escape($value);
                            $statement .= "{$value}";
                        }else{
                            $value = $this->escape($value);
                            $key = $this->escape($key);
                            if ($key != $value){
                                $statement .= "{$key} AS '{$value}'";
                            }else{
                                $statement .= "{$key}";
                            }
                        }
                        
                        $first = false;
                    }
                    
                    $statement .= "\n";
                    
                    /* Set */
                    $set = $sql->set;
                    $statement .= "SET ";
                    $first = true;
                    
                    foreach($set as $field => $value){
                        if (!$first) $statement .= ",\n";
                        $statement .= "{$field} = ";
                        
                        if ($value instanceof sql){
                            $statement .= $this->render_sql($value);
                        }else{
                            $statement .= "{$value}";
                        }
                        
                        $first = false;
                    }
                    $statement .= "\n";
                    
                    /* Where */
                    $where = $sql->where_conditions;
                    if (isset($where) && count($where)){
                        $statement .= "WHERE ";
                        foreach($where as $item){
                            if ($item instanceof sql){
                                $statement .= $this->render_sql($item);
                            }else{
                                $statement .= $item;
                            }
                        }
                    }
                    $statement .= ";\n";
                    
                    return $statement;
                }
                
                /* Delete statement */
                if (count($sql->delete_from_tables)){
                    $statement = "DELETE FROM `" . implode("`, `", $sql->delete_from_tables) . "`\n";

                    /* Where */
                    $where = $sql->where_conditions;
                    if (isset($where) && count($where)){
                        $statement .= "WHERE ";
                        foreach($where as $item){
                            if ($item instanceof sql){
                                $statement .= $this->render_sql($item);
                            }else{
                                $statement .= $item;
                            }
                        }
                    }
                    $statement .= ";\n";
                    
                    return $statement;
                }
    
                /* Create database */
                if(!is_null($sql->create_database_name)){
                    /*
                     * Create database
                     */
                    $statement = "CREATE DATABASE " . $this->escape($sql->create_database_name) . ";\n";
                    return $statement;
                }

                /* Drop database */
                if(!is_null($sql->drop_database_name)){
                    /*
                     * Create database
                     */
                    $statement = "DROP DATABASE " . $this->escape($sql->drop_database_name) . ";\n";
                    return $statement;
                }

                /* Drop table */
                if(!is_null($sql->drop_table_name)){
                    /*
                     * Create database
                     */
                    $statement = "DROP TABLE " . $this->escape($sql->drop_table_name) . ";\n";
                    return $statement;
                }
                
                /* Create table */
                if(!is_null($sql->create_table_name)){
                    
                    /*
                     * Create table
                     */
                    $statement = "CREATE TABLE {$sql->create_table_name} (\n";
                    $fields = $sql->create_table_fields;
                    $primary_keys = $sql->primary_keys;
                    $first = true;
                    
                    /* Add the fields */
                    foreach($fields as $field){
                        if (!$first) $statement .= ",\n";
                        
                        $auto_increment = false;
                        foreach($primary_keys as $key){
                            if ($key['field_name'] == $field['field_name'] && $key['auto_increment'] == true){
                                $auto_increment = true;
                                break;
                            }
                        }
                        
                        if ($auto_increment){
                            $statement .= $field['field_name'] . " SERIAL";
                        }else{
                            $statement .= $field['field_name'] . " " . $this->convert_data_type($field['data_type'], $field['signed']);
                            if ($field['nullable'] === false) $statement .= " NOT NULL";
                            if ($field['unique'] === true){
                                $statement .= " UNIQUE";
                            }
                            if (!is_null($field['default_value'])) $statement .= " DEFAULT'" . $this->escape($field['default_value']) . "'";
                        }
                        
                        $first = false;
                    }
                    
                    /* Add any primary keys */
                    $field_names = array();
                    foreach($primary_keys as $key) $field_names[] = $key['field_name'];
                    
                    if (count($field_names)){
                        $statement .= ",\nPRIMARY KEY (" . implode(", ", $field_names) . ")";
                    }
                    
                    /* Add foreign keys */
                    $foreign_keys = $sql->foreign_keys;
                    
                    foreach($foreign_keys as $key){
                        $statement .= ",\nFOREIGN KEY ({$key['field_name']}) REFERENCES {$key['reference_table_name']} ({$key['reference_field_name']}) ON DELETE {$key['on_delete']}";
                    }
                    
                    /* Add indexes */
//                    $indexes = $sql->indexes;
//                    
//                    foreach($indexes as $index){
//                        $statement .= ",\nINDEX ({$index['field_name']}";
//                        if (!is_null($index['size']) && is_numeric($index['size'])){
//                            $statement .= "({$index['size']})";
//                        }
//                        $statement .= ")";
//                    }

                    $statement .= "\n)";

                    $statement .= ";\n";

                    return $statement;
                }

                /*
                 * Alter table
                 */
                if (!is_null($sql->alter_table_name)){
                    $statement = "ALTER TABLE {$sql->alter_table_name}\n";
                    $fields = $sql->alter_table_fields;
                    $first = true;
                    
                    foreach($fields as $field){
                        if (!$first) $statement .= ",\n";
                        
                        switch($field['_type']){
                        case "add":
                            $statement .= "ADD " . $field['field_name'] . " " . $this->convert_data_type($field['data_type'], $field['signed']);
                            if ($field['nullable'] === false) $statement .= " NOT NULL";
                            if (!is_null($field['default_value'])) $statement .= " DEFAULT '" . $this->escape($field['default_value']) . "'";
                            if (!is_null($field['_after'])) $statement .= " AFTER {$field['_after']}";
                            break;
                        case "change":
                            $statement .= "CHANGE {$field['old_field_name']} " . $field['field_name'] . " " . $this->convert_data_type($field['data_type'], $field['signed']);
                            if ($field['nullable'] === false) $statement .= " NOT NULL";
                            if (!is_null($field['default_value'])) $statement .= " DEFAULT '" . $this->escape($field['default_value']) . "'";
                            if (!is_null($field['_after'])) $statement .= " AFTER {$field['_after']}";
                            break;
                        case "drop":
                            $statement .= "DROP " . $field['field_name'];
                            break;
                        }
                        
                        $first = false;
                    }

                    // Add in any foreign keys needed
                    foreach ($sql->foreign_keys as $foreign_key) {
                        if (!$first) {
                            $statement .= ",\n";
                            $first = false;
                        }
                        $statement .= "ADD FOREIGN KEY ({$foreign_key['field_name']})\n";
                        $statement .= "REFERENCES {$foreign_key['reference_table_name']}({$foreign_key['reference_field_name']})\n";
                        $statement .= "ON DELETE {$foreign_key['on_delete']}\n";
                        $statement .= "ON UPDATE CASCADE\n"; // TODO - we might want to think about this but there is no on_update captured in the $foreign_key array
                    }

                    $statement .= ";\n";

                    // TODO - thought needs to go into what happens when an FK is removed or the columns involved are altered

                    return $statement;
                }
            }
            
            
            return $statement;
        }
        
        /**
         * Renders a sql insert statement
         * 
         * @access public
         * @param sql
         * @return string
         */
        public function render_sql_insert($sql){
            $statement = "";
            
            /* Insert statement */
            if (in_array($sql->insert_into_table_name, array_merge(array('data_type', 'field'), $this->get_dataset_list()))){
                $statement = "INSERT INTO {$sql->insert_into_table_name}\n";
                $insert_fields = $sql->insert_into_fields;
                if (is_array($insert_fields)){
                    $statement .= "(";
                    $first = true;
                    foreach($insert_fields as $field){
                        if (!$first){
                            $statement .= ", ";
                        }

                        $field = $this->escape($field);
                        $statement .= "{$field}";
                        $first = false;
                    }

                    $statement .= ")\n";
                }

                /* Are we inserting values or a select? */
                if (is_array($sql->insert_into_values) && count($sql->insert_into_values)){
                    /* Insert the values */
                    $keys = array();

                    $statement .= "VALUES\n";

                    if (is_array($insert_fields)){
                        $keys = $insert_fields;
                    }else{
                        //Get the fields for this table
                        $keys = array_keys($schema); //TODO: BUG: $schema is not defined!
                    }

                    $rows = $sql->insert_into_values;
                    $first_row = true;

                    for($j = 0; $j < count($rows); $j++){
                        $row = $rows[$j];

                        if (count($row) == count($keys)){
                            if ($first_row){
                                $statement .= "(";
                                $first_row = false;
                            }else{
                                $statement .= ",\n(";
                            }

                            for($i = 0; $i < count($row); $i++){
                                $value = $row[$i];
                                $key = $keys[$i];

                                if ($i > 0) $statement .= ", ";

                                if ($value instanceof \adapt\sql){
                                    $statement .= $this->render_sql($value);
                                }elseif(is_string($value) || is_numeric($value)){

                                    /* Escape the value */
                                    $value = $this->escape($value);

                                    $statement .= "'{$value}'";
                                }elseif (is_bool($value)) {
                                    if ($value) {
                                        $statement .= "true";
                                    } else {
                                        $statement .= "false";
                                    }
                                } else {
                                    $statement .= "DEFAULT";
                                }
                            }

                            $statement .= ")";
                        }else{
                            //Fail the entire insert
                            $this->error("Unable in insert data into '{$sql->insert_into_table_name}' row " . ($j + 1) . " column count is incorrect");
                            return null;
                        }
                    }
                    
                    if (count($rows) == 1){
                        $statement .= "\nRETURNING {$sql->insert_into_table_name}_id AS id;";
                        $this->_last_rendered_insert_statement = $statement;
                    }else{
                        $statement .= ";";
                        $this->_last_rendered_insert_statement = null;
                    }
                    
                    return $statement;
                }
            }else{
                $this->error("Unable to insert data into non-existant table '{$sql->insert_into_table_name}'");
                return null;
            }
        }
        
        /**
         * Breaks a native data type into it's parts
         * 
         * @access public
         * @param string
         * The native type
         * @return array
         * Returns an array of the data types parts
         */
        public function parse_data_type($native_data_type){
            $params = [];
            
            if (mb_stripos($native_data_type, "(") !== false){
                $native_data_type = preg_replace("/\)/", "", $native_data_type);
                list($native_data_type, $raw_params) = explode("(", $native_data_type);
                $params = explode(",", $raw_params);
                foreach($params as &$p) $p = mb_trim($p);
            }
            
            $native_data_type = mb_trim(mb_strtolower($native_data_type));
            return [
                'data_type' => strtolower($native_data_type),
                'params' => $params
            ];
        }
        
        /**
         * Converts a data type from a string into an array
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
            $params = array();
            
            if (mb_stripos($type, "(") !== false){
                $type = preg_replace("/\)/", "", $type);
                list($type, $raw_params) = explode("(", $type);
                $params = explode(",", $raw_params);
                foreach($params as &$p) $p = mb_trim($p);
            }
            
            $type = mb_trim(mb_strtolower($type));
            
            switch($type){
            case "tinyint":
            case "smallint":
            case "mediumint":
            case "int":
            case "integer":
            case "bigint":
                $type = mb_strtoupper($type);
                return $type;
            
            case "serial":
                return "SERIAL";
            
            case "bit":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0]) && $params[0] >= 1 && $params[0] <= 64){
                    $type .= "({$params[0]})";
                }else{
                    //Throw error
                }
                break;
            
            case "boolean":
            case "bool":
                return "BOOL";
            
            case "decimal":
            case "double":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0])){
                    $type .= "({$params[0]})";
                }elseif (count($params) == 2 && is_numeric($params[0]) && is_numeric($params[1])){
                    $type .= "({$params[0]},{$params[1]})";
                }else{
                    //Throw error
                    return;
                }
                return $type;
                
            case "float":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0])){
                    $type .= "({$params[0]})";
                }else{
                    //Throw error
                    return;
                }
                if (!$signed) $type .= " UNSIGNED";
                if ($zero_fill) $type .= " ZEROFILL";
                return $type;
                
            case "char":
            case "binary":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0]) && $params[0] >= 0 && $params[0] <= 255){
                    $type .= "({$params[0]})";
                }else{
                    //Throw error
                    return;
                }
                return $type;
            
            case "varchar":
            case "varbinary":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0]) && $params[0] >= 0 && $params[0] <= 65535){
                    $type .= "({$params[0]})";
                }else{
                    //Throw error
                    return;
                }
                return $type;
            
            case "tinyblob":
                $type = mb_strtoupper($type);
                return $type;
            
            case "blob":
                $type = mb_strtoupper($type);
                if (count($params) == 1 && is_numeric($params[0]) && $params[0] >= 0 && $params[0] <= 65535){
                    $type .= "({$params[0]})";
                }
                return $type;
            
            case "mediumblob":
            case "longblob":
            case "tinytext":
            case "text":
            case "mediumtext":
            case "longtext":
                $type = mb_strtoupper($type);
                return $type;
            
            case "enum":
            //case "set":
                $type = mb_strtoupper($type);
                
                $type .= "(";
                if (count($params) > 0){
                    for($i = 0; $i < count($params); $i++){
                        if ($i > 0) $type .= ", ";
                        $type .= "'" . $this->escape(mb_trim($params[$i], '\s\'"')) . "'";
                    }
                }
                $type .= ")";
                
                $name = strtolower(preg_replace("/[^A-Za-z]/", "", $type));
                
                $sql = "CREATE TYPE {$name} AS {$type};";
                
                $errors = $this->errors();
                $this->query($sql);
                $this->errors(true);
                if (count($errors)) $this->error($errors);
                
                return $name;
                
            case "year":
            case "date":
            case "time":
                $type = mb_strtoupper($type);
                return $type;
            case "datetime":
            case "timestamp":
                return "TIMESTAMP";
            default:
                /*
                 * We are going to seek the
                 * base type from the schema's data_types
                 */
                foreach($this->_data_types as $data_type){
                    if ($data_type['name'] == $type && isset($data_type['based_on_data_type'])){
                        return $this->convert_data_type($data_type['based_on_data_type']);
                    }
                }
            }
        }
    }
}
