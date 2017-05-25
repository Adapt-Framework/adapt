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

/*
 * Prevent direct access
 */
defined('ADAPT_STARTED') or die;

/*
 * Array functions
 */

function is_assoc($array){
    if (is_array($array)){
        return array_keys($array) !== range(0, count($array) - 1);
    }
    return false;
}

function array_remove($array, $index){
    if ((is_array($array)) && (!is_assoc($array))){
        $output = array();
        for($i = 0; $i < count($array); $i++){
            if ($i != $index) $output[] = $array[$i];
        }
        return $output;
    }
    
    return $array;
}



/*
 * JSON functions
 */
function is_json($data){
    json_decode($data, true);
    return (json_last_error() == JSON_ERROR_NONE);
}

/*
 * Text functions
 */
function mb_trim($string, $trim_chars = '\s'){
    return preg_replace('/^['.$trim_chars.']*(?U)(.*)['.$trim_chars.']*$/u', '\\1',$string);
}


function q($string){
    if (is_string($string)){
        $adapt = $GLOBALS['adapt'];
        if ($adapt && $adapt instanceof \adapt\base){
            if (isset($adapt->data_source) && $adapt->data_source instanceof \adapt\data_source_sql){
                return "\"" . $adapt->data_source->escape($string) . "\"";
            }else{
                return "\"" . addcslashes($string, "\"") . "\"";
            }
        }
    }
    
    return "\"\"";
}

/**
* Returns a GUIDv4 string
*
* Uses the best cryptographically secure method 
* for all supported platforms with fallback to an older, 
* less secure version.
* 
* Updated in Adapt v2.1 to register the GUID
*
* @author Dave Pearson - via PHP.net
* @author Matt Bruton 
* @param bool $trim
* @return string
*/
function guid($table_name = null){
    $guid = null;
    if (function_exists('com_create_guid')){
        $guid = trim(com_create_guid(), '{}');
    }
    
    if (is_null($guid) && function_exists('openssl_random_pseudo_bytes')){
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $guid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    if (is_null($guid)){
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $lbrace = $trim ? "" : chr(123);
        $rbrace = $trim ? "" : chr(125);
        $guidv4 = $lbrace .
            substr($charid,  0,  8).$hyphen .
            substr($charid,  8,  4).$hyphen .
            substr($charid, 12,  4).$hyphen .
            substr($charid, 16,  4).$hyphen .
            substr($charid, 20, 12) .
            $rbrace;
        $guid = $guidv4;
    }
    
    if (!is_null($table_name) && !is_null($guid)){
        $adapt = $GLOBALS['adapt'];

        if ($adapt instanceof \adapt\base && $adapt->data_source instanceof \adapt\data_source_sql){
            /* Check if the GUID already exists */
            $sql = $adapt->data_source->sql;
            $sql->select('*')
                ->from('guid')
                ->where(
                    new sql_and(
                        new sql_cond('date_delete', sql::IS, sql::NULL),
                        new sql_cond('guid', sql::EQUALS, q($guid))
                    )
                );
            if (count($sql->execute(0)->results())){
                /* Retry */
                return guid($table_name);
            }else{
                $sql->insert_into('guid', ['guid', 'table_name'])
                    ->values($guid, $table_name)
                    ->execute();
            }
        }
    }
    
    return $guid;
}


?>